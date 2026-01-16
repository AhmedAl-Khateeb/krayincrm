<?php

namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\Request;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Resources\ActivityResource;
use Webkul\Admin\Notifications\LeadNoteAdded;
use Webkul\Email\Repositories\AttachmentRepository;
use Webkul\Email\Repositories\EmailRepository;
use Webkul\Lead\Repositories\LeadRepository;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected EmailRepository $emailRepository,
        protected AttachmentRepository $attachmentRepository,
        protected LeadRepository $leadRepository
    ) {}

    /**
     * List activities for a lead.
     */
    public function index($id)
    {
        $activities = $this->activityRepository
            ->leftJoin('lead_activities', 'activities.id', '=', 'lead_activities.activity_id')
            ->where('lead_activities.lead_id', (int) $id)
            ->get();

        return ActivityResource::collection(
            $this->concatEmailAsActivities((int) $id, $activities)
        );
    }

    /**
     * ✅ Store Note/Activity for lead + notify assigned user.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'comment' => ['required', 'string', 'min:1'],
            'title'   => ['nullable', 'string', 'max:191'],
            'type'    => ['nullable', 'string'],
        ]);

        $admin = auth()->guard('user')->user();

        // ✅ lead
        $lead = $this->leadRepository->findOrFail((int) $id);

        // ✅ create activity as note
        $activity = $this->activityRepository->create([
            'title'         => $request->input('title') ?: 'Note',
            'type'          => $request->input('type') ?: 'note',
            'comment'       => $request->input('comment'),
            'is_done'       => 1,
            'schedule_from' => null,
            'schedule_to'   => null,
            'location'      => null,
            'additional'    => null,
            'user_id'       => $admin->id,
        ]);

        /**
         * ✅ Attach lead to activity using pivot relation
         * IMPORTANT: لا تعمل insert يدوي + sync في نفس الوقت
         */
        $activity->leads()->syncWithoutDetaching([(int) $lead->id]);

        /**
         * ✅ notify assigned user (owner of lead)
         */
        $assigned = $lead->user ?? null;

        if ($assigned && (int) $assigned->id !== (int) $admin->id) {
            // ✅ build URL safely (no route dependency)
            $adminBase = url('/' . trim(config('app.admin_url') ?: 'admin', '/'));
            $leadUrl = $adminBase . '/leads/view/' . $lead->id;

            $assigned->notify(new LeadNoteAdded([
                'type'       => 'lead_note_added',
                'lead_id'    => $lead->id,
                'lead_title' => $lead->title,
                'note'       => $activity->comment,
                'by'         => [
                    'id'   => $admin->id,
                    'name' => $admin->name,
                ],
                'url'        => $leadUrl,
                'created_at' => now()->toDateTimeString(),
            ]));
        }

        return response()->json([
            'status'  => 'ok',
            'message' => 'Note added',
            'data'    => new ActivityResource($activity),
        ]);
    }

    /**
     * Emails -> map as activities
     */
    public function concatEmailAsActivities($leadId, $activities)
    {
        $emails = \DB::table('emails as child')
            ->select('child.*')
            ->join('emails as parent', 'child.parent_id', '=', 'parent.id')
            ->where('parent.lead_id', $leadId)
            ->union(\DB::table('emails as parent')->where('parent.lead_id', $leadId))
            ->get();

        return $activities->concat($emails->map(function ($email) {
            return (object) [
                'id'            => $email->id,
                'parent_id'     => $email->parent_id,
                'title'         => $email->subject,
                'type'          => 'email',
                'is_done'       => 1,
                'comment'       => $email->reply,
                'schedule_from' => null,
                'schedule_to'   => null,
                'user'          => auth()->guard('user')->user(),
                'participants'  => [],
                'location'      => null,
                'additional'    => [
                    'folders' => json_decode($email->folders),
                    'from'    => json_decode($email->from),
                    'to'      => json_decode($email->reply_to),
                    'cc'      => json_decode($email->cc),
                    'bcc'     => json_decode($email->bcc),
                ],
                'files'         => $this->attachmentRepository
                    ->findWhere(['email_id' => $email->id])
                    ->map(function ($attachment) {
                        return (object) [
                            'id'         => $attachment->id,
                            'name'       => $attachment->name,
                            'path'       => $attachment->path,
                            'url'        => $attachment->url,
                            'created_at' => $attachment->created_at,
                            'updated_at' => $attachment->updated_at,
                        ];
                    }),
                'created_at'    => $email->created_at,
                'updated_at'    => $email->updated_at,
            ];
        }))->sortByDesc('id')->sortByDesc('created_at');
    }
}
