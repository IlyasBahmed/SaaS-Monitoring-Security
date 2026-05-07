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
    ->subject('You’ve been invited to CyberShield')
    ->greeting('Welcome to CyberShield!')

    ->line('You’ve been invited to join **CyberShield**, your Security Operations platform.')

    ->line('To get started, you need to create your account password and activate your access.')

    ->action('Activate your account', $url)

    ->line('This secure link will allow you to define your password and access your workspace.')

    ->line('If you did not expect this invitation, you can safely ignore this email.');

    }
}
