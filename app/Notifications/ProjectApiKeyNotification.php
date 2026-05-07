<?php

namespace App\Notifications;

use App\Models\Projects;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectApiKeyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Projects $project,
        public string $apiKey
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your project API key')
            ->view('emails.project-api-key', [
                'apiKey' => $this->apiKey,
                'client' => $notifiable,
                'project' => $this->project,
            ]);
    }
}
