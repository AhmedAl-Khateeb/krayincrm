<?php

namespace Webkul\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LeadNoteAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $lead,
        public $activity,
        public $createdBy
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'lead_note_added',
            'lead_id' => $this->lead->id,
            'activity_id' => $this->activity->id,
            'title' => 'New note added',
            'message' => "New note added on Lead: {$this->lead->title}",
            'by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ],
            // optional: عشان تعمل route من الجرس
            'url' => route('admin.leads.view', $this->lead->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
