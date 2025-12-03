<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewMeetingNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $meeting;
    protected $picName;

    /**
     * Create a new notification instance.
     */
    public function __construct($meeting, $picName)
    {
        $this->meeting = $meeting;
        $this->picName = $picName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'meeting_id' => $this->meeting->id_rapat,
            'title' => $this->meeting->judul,
            'pic_name' => $this->picName,
            'date' => $this->meeting->tanggal,
            'message' => "PIC {$this->picName} mengajukan rapat: {$this->meeting->judul} untuk tanggal " . date('d/m/Y', strtotime($this->meeting->tanggal)),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'meeting_id' => $this->meeting->id_rapat,
            'title' => $this->meeting->judul,
            'pic_name' => $this->picName,
            'date' => $this->meeting->tanggal,
            'message' => "PIC {$this->picName} mengajukan rapat: {$this->meeting->judul} untuk tanggal " . date('d/m/Y', strtotime($this->meeting->tanggal)),
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
