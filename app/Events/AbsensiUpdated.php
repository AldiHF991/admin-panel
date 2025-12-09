<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
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
     * ID Rapat untuk channel broadcasting.
     *
     * @var string
     */
    public $rapatId;

    /**
     * Create a new event instance.
     *
     * @param  string  $rapatId
     * @param  mixed  $absensi
     */
    public function __construct($rapatId, $absensi)
    {
        $this->rapatId = $rapatId;
        $this->absensi = $absensi;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Menggunakan rapatId yang diterima dari constructor
        return new Channel('meeting.' . $this->rapatId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Data absensi sudah di-load dengan relasi dari controller
        // Cukup konversi ke array untuk broadcasting
        return [
            'absensi' => is_array($this->absensi) ? $this->absensi : $this->absensi->toArray(),
            'rapatId' => $this->rapatId,
        ];
    }

    /**
     * Get the event name for broadcasting.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'attendance.marked';
    }
}
