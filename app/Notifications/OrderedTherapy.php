<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $doctorId = $this->therapy->doctor->user->id;

        if ($notifiable->id === $doctorId) {
            return ['mail', 'database'];
        }

        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable)
    {
        return [
            'message' => 'Pasien ('.$this->therapy->patient->name.
                ') baru saja memilih '.' Psikolog ('.$this->therapy->doctor->user->name.
                ') untuk melakukan terapi.',
        ];
    }

    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject('Terapi Baru Dipesan')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Pasien '.$this->therapy->patient->name.' telah memilih anda sebagai Psikolog untuk melakukan terapi.')
            ->action('Lihat Terapi', route('doctor.therapies.in_progress.index'))
            ->line('Terima kasih telah menggunakan layanan kami!');
    }
}
