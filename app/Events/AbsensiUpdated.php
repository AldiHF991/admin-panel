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
     * Create a new event instance.
     *
     * @param  mixed  $absensi
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
        // PERBAIKAN: Menggunakan Channel publik agar sesuai dengan channels.php dan frontend.
        // Otorisasi sudah diatur di channels.php (return true).
        // Mengambil id_rapat dari data absensi. Diasumsikan $this->absensi adalah collection.
        $rapatId = $this->absensi->first()->id_rapat ?? 'default';

        return new Channel('Absensi.Rapat.'.$rapatId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // 1. Eager load relasi 'attendable' untuk semua item.
        $this->absensi->load('attendable');

        // 2. Muat relasi 'division' hanya untuk item yang merupakan User.
        $this->absensi->where('attendable_type', \App\Models\User::class)
            ->load('attendable.division');

        return ['absensi' => $this->absensi->values()];
    }
}
