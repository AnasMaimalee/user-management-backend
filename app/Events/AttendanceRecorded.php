<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DailyAttendance;

class AttendanceRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attendance;

    public function __construct(DailyAttendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('attendance.hr'); // HR/Admin channel
    }

    public function broadcastAs()
    {
        return 'attendance.recorded';
    }
}
