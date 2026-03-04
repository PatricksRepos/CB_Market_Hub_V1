<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SimpleNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $url,
        public string $message = ''
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'message' => $this->message,
        ];
    }
}
