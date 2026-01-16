<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadNoteAdded extends Notification
{
    use Queueable;

    public function __construct(
        public $leadId,
        public $leadTitle,
        public $noteText,
        public $actorName
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New note added',
            'body'  => "{$this->actorName} added a note on: {$this->leadTitle}",
            'note'  => mb_strimwidth($this->noteText, 0, 140, '...'),
            'url'   => url("/admin/leads/view/{$this->leadId}"),
            'lead_id' => $this->leadId,
        ];
    }
}
