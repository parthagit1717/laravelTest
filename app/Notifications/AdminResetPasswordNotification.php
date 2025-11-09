<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

// CRITICAL: We extend the base ResetPassword notification
class AdminResetPasswordNotification extends ResetPassword 
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token; // Declare the token property (inherited, but good practice to define if you add a constructor)

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        // CRITICAL FIX: Call the parent constructor to store the token
        parent::__construct($token); 
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Get the token from the property inherited/set by the parent constructor
        $token = $this->token;

        // Ensure the correct admin route is used
        $url = url(route('admin.password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Admin Password Reset Notification')
            ->line('You are receiving this email because we received a password reset request for your admin account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}