<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceMarked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idRapat;
    public $idUser;
    public $namaUser;
    public $waktuAbsen;
    public $totalAttendees;

    /**
     * Create a new event instance.
     */
    public function __construct($idRapat, $idUser, $namaUser, $waktuAbsen, $totalAttendees)
    {
        $this->idRapat = $idRapat;
        $this->idUser = $idUser;
        $this->namaUser = $namaUser;
        $this->waktuAbsen = $waktuAbsen;
        $this->totalAttendees = $totalAttendees;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('meeting.' . $this->idRapat),
            new Channel('meetings'), // Global channel for all dashboards
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'attendance.marked';
    }
}
