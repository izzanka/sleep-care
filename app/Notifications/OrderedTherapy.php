<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderedTherapy extends Notification
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
        return 'ordered-therapy';
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
            'message' => 'Pasien ('.$this->therapy->patient->name.
                ') baru saja memilih '.' Psikolog ('.$this->therapy->doctor->user->name.
                ') untuk melakukan terapi.',
        ];
    }
}
