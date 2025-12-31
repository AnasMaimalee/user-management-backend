<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\BiometricDevice;
use Illuminate\Http\Request;
use Jmrashed\Zkteco\Lib\ZKTeco;

class BiometricEnrollmentController extends Controller
{
    public function enroll(Employee $employee)
    {
        $device = BiometricDevice::where('is_active', true)->firstOrFail();

        $zk = new ZKTeco($device->ip, $device->port);
        $zk->connect();

        // Assign UID if not exists
        $uid = $employee->biometric_uid ?? rand(1000, 9999);

        // Push user to device
        $zk->setUser(
            $uid,
            $employee->employee_code,
            $employee->full_name,
            '',     // password (not needed)
            0       // role: normal user
        );

        // Save UID in DB
        $employee->update([
            'biometric_uid' => $uid,
        ]);

        $zk->disconnect();

        return response()->json([
            'message' => 'Employee sent to biometric device. Ask employee to enroll fingerprint on device.',
            'uid' => $uid
        ]);
    }
}
