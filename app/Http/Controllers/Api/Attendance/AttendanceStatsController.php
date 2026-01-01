<?php


namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceStatsService;

class AttendanceStatsController extends Controller
{
    public function index(AttendanceStatsService $service)
    {
        return response()->json([
            'data' => $service->getTodayStats()
        ]);
    }
}
