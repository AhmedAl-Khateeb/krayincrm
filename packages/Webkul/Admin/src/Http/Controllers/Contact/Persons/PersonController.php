<?php

namespace Webkul\Admin\Http\Controllers\Contact\Persons;

use App\Support\VisibleUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;
use Webkul\Admin\DataGrids\Contact\PersonDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\AttributeForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Resources\PersonResource;
use Webkul\Contact\Repositories\PersonRepository;

class PersonController extends Controller
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository)
    {
        request()->request->add(['entity_type' => 'persons']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(PersonDataGrid::class)->process();
        }

        return view('admin::contacts.persons.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::contacts.persons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request): RedirectResponse|JsonResponse
    {
        Event::dispatch('contacts.person.create.before');

        $data = $request->all();

        $me = auth()->guard('admin')->user();

        $isAdmin = optional($me->role)->permission_type === 'all';
        if (!$isAdmin) {
            $data['user_id'] = $me->id;
        } else {
            if (empty($data['user_id'])) {
                $data['user_id'] = $me->id;
            }
        }

        $userIds = VisibleUsers::ids();
        $orgId = $data['organization_id'] ?? null;

        if ($orgId) {
            $org = $this->organizationRepository->find($orgId);

            if (!$org || !in_array($org->user_id, $userIds)) {
                abort(403);
            }
        }

        $person = $this->personRepository->create($data);

        Event::dispatch('contacts.person.create.after', $person);

        if (request()->ajax()) {
            return response()->json([
                'data' => $person,
                'message' => trans('admin::app.contacts.persons.index.create-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.contacts.persons.index.create-success'));

        return redirect()->route('admin.contacts.persons.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $person = $this->personRepository->findOrFail($id);
        $userIds = VisibleUsers::ids();

        if (!in_array($person->user_id, $userIds)) {
            abort(403);
        }

        return view('admin::contacts.persons.view', compact('person'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $person = $this->personRepository->findOrFail($id);

        $userIds = (array) (VisibleUsers::ids() ?? []);
        if (!empty($person->user_id) && !in_array($person->user_id, $userIds)) {
            abort(403);
        }

        // ✅ حمّل attribute_values بس… من غير options
        $person->load([
            'organization',
            'attributeValues' => fn ($q) => $q->with('attribute.options'),
        ]);

        $attributes = app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
            ['code', 'NOTIN', ['organization_id']],
            'entity_type' => 'persons',
        ]);

        return view('admin::contacts.persons.edit', compact('person', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse|JsonResponse
    {
        Event::dispatch('contacts.person.update.before', $id);

        // ✅ هات الشخص الأول
        $person = $this->personRepository->findOrFail($id);

        // ✅ Permission check (نفس بتاع organizations)
        $userIds = VisibleUsers::ids();

        if (!empty($person->user_id) && !in_array($person->user_id, $userIds)) {
            abort(403);
        }

        $data = $request->all();

        // ✅ لو اليوزر مش متحدد خالص اربطه بالمستخدم الحالي
        if (empty($data['user_id'])) {
            $data['user_id'] = auth()->id(); // ✅ بدون guard
        }

        // ✅ امنع تغيير organization لواحدة مش تبع نفس اليوزر
        $orgId = $data['organization_id'] ?? null;

        if ($orgId) {
            $org = $this->organizationRepository->find($orgId);

            if (!$org || !in_array($org->user_id, $userIds)) {
                abort(403);
            }
        }

        // ✅ نفّذ التحديث بالـ $data (مش request()->all())
        $person = $this->personRepository->update($data, $id);

        Event::dispatch('contacts.person.update.after', $person);

        if (request()->ajax()) {
            return response()->json([
                'data' => $person,
                'message' => trans('admin::app.contacts.persons.index.update-success'),
            ], 200);
        }

        session()->flash('success', trans('admin::app.contacts.persons.index.update-success'));

        return redirect()->route('admin.contacts.persons.index');
    }

    /**
     * Search person results.
     */
    public function search(): JsonResource
    {
        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $persons = $this->personRepository
                ->pushCriteria(app(RequestCriteria::class))
                ->findWhereIn('user_id', $userIds);
        } else {
            $persons = $this->personRepository
                ->pushCriteria(app(RequestCriteria::class))
                ->all();
        }

        return PersonResource::collection($persons);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if (
            $person->leads
            && $person->leads->count() > 0
        ) {
            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-failed'),
            ], 400);
        }

        try {
            Event::dispatch('contacts.person.delete.before', $person);

            $person->delete();

            Event::dispatch('contacts.person.delete.after', $person);

            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass destroy the specified resources from storage.
     */
    public function massDestroy(MassDestroyRequest $request): JsonResponse
    {
        try {
            $persons = $this->personRepository->findWhereIn('id', $request->input('indices', []));

            $deletedCount = 0;

            $blockedCount = 0;

            foreach ($persons as $person) {
                if (
                    $person->leads
                    && $person->leads->count() > 0
                ) {
                    ++$blockedCount;

                    continue;
                }

                Event::dispatch('contact.person.delete.before', $person);

                $this->personRepository->delete($person->id);

                Event::dispatch('contact.person.delete.after', $person);

                ++$deletedCount;
            }

            $statusCode = 200;

            switch (true) {
                case $deletedCount > 0 && $blockedCount === 0:
                    $message = trans('admin::app.contacts.persons.index.all-delete-success');

                    break;

                case $deletedCount > 0 && $blockedCount > 0:
                    $message = trans('admin::app.contacts.persons.index.partial-delete-warning');

                    break;

                case $deletedCount === 0 && $blockedCount > 0:
                    $message = trans('admin::app.contacts.persons.index.none-delete-warning');

                    $statusCode = 400;

                    break;

                default:
                    $message = trans('admin::app.contacts.persons.index.no-selection');

                    $statusCode = 400;

                    break;
            }

            return response()->json(['message' => $message], $statusCode);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-failed'),
            ], 400);
        }
    }
}
