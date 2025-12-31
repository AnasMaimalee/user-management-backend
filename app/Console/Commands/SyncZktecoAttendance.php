<?php

// app/Console/Commands/SyncZktecoAttendance.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{
    AttendanceLog,
    Employee,
    BiometricDevice
};
use App\Services\Biometric\ZktecoService;
use Carbon\Carbon;

class SyncZktecoAttendance extends Command
{
    protected $signature = 'zk:sync-attendance';
    protected $description = 'Sync attendance logs from ZKTeco devices';

    public function handle()
    {
        $devices = BiometricDevice::where('is_active', true)->get();

        foreach ($devices as $device) {
            $this->syncDevice($device);
        }

        $this->info('ZKTeco attendance sync completed.');
    }

    protected function syncDevice(BiometricDevice $device)
    {
        $service = new ZktecoService($device->ip, $device->port);

        if (!$service->connect()) {
            $this->error("Failed to connect to {$device->name}");
            return;
        }

        $logs = $service->getAttendance();

        foreach ($logs as $log) {
            $employee = Employee::where(
                'device_user_id',
                $log['id']
            )->first();

            if (!$employee) {
                continue; // unknown user
            }

            AttendanceLog::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'punched_at' => Carbon::parse($log['timestamp']),
                ],
                [
                    'device_user_id' => $log['id'],
                    'biometric_device_id' => $device->id,
                ]
            );
        }

        $service->disconnect();
    }
}
