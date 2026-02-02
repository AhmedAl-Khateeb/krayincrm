<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NextCallDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $entityType,
        public int $entityId,
        public string $entityName,
        public string $callDate,
        public ?string $phone = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $url = match ($this->entityType) {
            'organization' => route('admin.contacts.organizations.edit', $this->entityId),
            'lead' => route('admin.leads.view', $this->entityId),
            'person' => route('admin.contacts.persons.edit', $this->entityId),
            default => '#',
        };

        return [
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'title' => 'موعد الاتصال بـ '.$this->entityName,
            'message' => "لازم تتصل بـ {$this->entityName} (موعد: {$this->callDate})",
            'phone' => $this->phone,
            'url' => $url,
        ];
    }

    public function toArray($notifiable)
    {
        $url = match ($this->entityType) {
            'organization' => route('admin.contacts.organizations.edit', $this->entityId),
            'lead' => route('admin.leads.view', $this->entityId),
            'person' => route('admin.contacts.persons.edit', $this->entityId),
            default => '#',
        };

        return [
            'title' => 'موعد الاتصال بـ '.$this->entityName,
            'message' => "اتصل بـ {$this->entityName} - {$this->phone}",
            'url' => $url,
            'phone' => $this->phone,
        ];
    }
}
