<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InviteUserNotification extends Notification
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/reset-password/'.$this->token.'?email='.$notifiable->email);

        return (new MailMessage)
            ->subject('CyberShield access invitation')
            ->greeting('Welcome to CyberShield')
            ->line('An administrator has invited you to join the CyberShield SOC workspace.')
            ->line('Use the secure link below to set your password and activate your account.')
            ->action('Set your password', $url)
            ->line('If you were not expecting this invitation, you can safely ignore this message.');
    }
}
