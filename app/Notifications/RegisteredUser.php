<?php

namespace App\Notifications;

use App\Enum\UserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegisteredUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function databaseType(object $notifiable): string
    {
        return 'registered-user';
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
            'message' => ($this->user->role === UserRole::DOCTOR->value ? 'Psikolog' : 'Pasien')
                .' ('.$this->user->name.') baru saja melakukan registrasi.',
        ];
    }
}
