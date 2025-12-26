<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:3001');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        // Use your custom Blade template
        return (new MailMessage)
            ->subject('Reset Your Maimalee HR Portal Password')
            ->view('emails.password-reset', [ // ← Custom view
                'resetUrl' => $resetUrl,
                'employee' => $notifiable,
            ]);
    }
}
