<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $absensi;

    /**
     * Create a new event instance.
     */
    public function __construct($absensi)
    {
        $this->absensi = $absensi;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('meeting.' . $this->absensi->id_rapat . '.attendance'),
        ];
    }

    public function broadcastAs()
    {
        return 'attendance.recorded';
    }

    public function broadcastWith()
    {
        $this->absensi->load('attendable');
        
        if ($this->absensi->attendable_type === 'App\\Models\\User') {
            $this->absensi->load('attendable.division');
        }
        
        return $this->absensi->toArray();
    }
}
