<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $rapat;
    protected $statusMessage;
    protected $note;

    /**
     * Create a new notification instance.
     */
    public function __construct($rapat, $statusMessage, $note = null)
    {
        $this->rapat = $rapat;
        $this->statusMessage = $statusMessage;
        $this->note = $note;
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
            'meeting_id' => $this->rapat->id_rapat,
            'title' => 'Status Rapat Diperbarui',
            'message' => $this->statusMessage . ': ' . $this->rapat->judul,
            'status' => $this->rapat->id_status,
            'note' => $this->note,
        ];
    }
}
