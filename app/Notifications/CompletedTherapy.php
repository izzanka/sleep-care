<?php

namespace App\Notifications;

use App\Enum\UserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompletedTherapy extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($therapy)
    {
        $this->therapy = $therapy;
    }

    public function databaseType(object $notifiable): string
    {
        return 'completed-therapy';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Psikolog ('.$this->therapy->doctor->user->name.
                ') baru saja menyelesaikan terapi dengan '.' Pasien ('.$this->therapy->patient->name.
                ').',
        ];
    }
}
