<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbsensiUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Data absensi yang akan di-broadcast.
     *
     * @var array
     */
    public $absensi;

    /**
     * Create a new event instance.
     *
     * @param mixed $absensi
     */
    public function __construct($absensi)
    {
        $this->absensi = $absensi;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Channel ini harus cocok dengan yang didengarkan di frontend.
        // Kita menggunakan PrivateChannel karena data absensi mungkin sensitif.
        // Nama channel dinamis berdasarkan ID rapat.
        $rapatId = $this->absensi->first()->rapat_id ?? 'default';
        return new PrivateChannel('Absensi.Rapat.' . $rapatId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['absensi' => $this->absensi];
    }
}
