<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->subject('Welcome to Our Website!')
    //         ->greeting('Hello ' . $notifiable->name . '!')
    //         ->line('Thank you for registering on our platform.')
    //         ->line('We are happy to have you with us.')
    //         ->action('Visit Website', url('/'))
    //         ->line('If you have any questions, feel free to contact us.');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome to Our Platform',
            'message' => 'Thank you for registering with us. We are excited to have you!',
            'type' => 'welcome', // login, order, payment, etc.
            // Notification Icon or Banner
            'image' => asset('notification-icon/png-transparent-tea-cup-color-gr.png'),
            // Deep link (Mobile Apps & Web)
            'deep_link' => [
                'app' => 'myapp://welcome', // Mobile App Deep Link
                'web' => url('/welcome'),   // Web Link
            ],
            // Extra meta data (for future use)
            'data' => [
                'user_id' => $notifiable->id??'',
                'email' => $notifiable->email??'',
            ],
            // Footer / Bottom text
            'footer' => 'Thanks for joining us!',
            // Time
            'timestamp' => now()->toISOString(),
        ];
    }
}
