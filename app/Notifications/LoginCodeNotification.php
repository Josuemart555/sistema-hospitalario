<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginCodeNotification extends Notification
{
    public function __construct(private string $code, private int $minutes) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Su código de verificación: '.$this->code)
            ->markdown('mail.login-code', ['name' => $notifiable->name, 'code' => $this->code, 'minutes' => $this->minutes]);
    }
}
