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
            ->subject('You are invited to CyberShield')
            ->greeting('Welcome to CyberShield')
            ->line('You have been invited to join the platform.')
            ->line('Click below to set your password and activate your account.')
            ->action('Set your password', $url)
            ->line('If you did not expect this invitation, ignore this email.');
    }
}