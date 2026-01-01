<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\BiometricDevice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Jmrashed\Zkteco\Lib\ZKTeco;

class BiometricEnrollmentController extends Controller
{
    public function enroll(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'biometric_secret' => 'required|string'
        ]);

        /** 🔐 Validate biometric secret */
        if ($request->biometric_secret !== config('services.biometric.secret')) {
            return response()->json([
                'message' => 'Unauthorized biometric action.'
            ], 403);
        }

        $employee = Employee::findOrFail($request->employee_id);

        /** ✅ Find active biometric device */
        $device = BiometricDevice::where('is_active', true)->first();

        if (!$device) {
            return response()->json([
                'message' => 'No active biometric device found.'
            ], 400);
        }

        try {
            $zk = new ZKTeco($device->ip, $device->port);

            if (!$zk->connect()) {
                return response()->json([
                    'message' => 'Failed to connect to biometric device.'
                ], 500);
            }

            /** 🔁 RESET IF ALREADY ENROLLED */
            if ($employee->biometric_uid) {
                $zk->removeUser($employee->biometric_uid);
            }

            /** 🔢 Generate UID if missing */
            $uid = $employee->biometric_uid ?? $this->generateUniqueUid();

            /** 👤 Send user to device */
            $zk->setUser(
                uid: $uid,
                userid: $employee->employee_code,
                name: trim($employee->first_name . ' ' . $employee->last_name),
                password: '',
                role: 0
            );

            /** 💾 Save enrollment info */
            $employee->update([
                'biometric_uid' => $uid,
                'fingerprint_enrolled_at' => null
            ]);

            $zk->disconnect();

            return response()->json([
                'message' => 'Employee enrollment initialized successfully.',
                'instructions' => 'Ask employee to scan fingerprint on device.',
                'uid' => $uid,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'employee_code' => $employee->employee_code
                ]
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Biometric enrollment failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // app/Http/Controllers/Api/BiometricController.php

    public function reset(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'biometric_secret' => 'required|string'
        ]);

        // Use services.secret as you discovered
        if ($request->biometric_secret !== config('services.secret')) {
            return response()->json(['message' => 'Invalid biometric secret'], 403);
        }

        $employee = Employee::findOrFail($request->employee_id);

        // Check if there's anything to reset
        if (!$employee->device_user_id && !$employee->fingerprint_enrolled_at) {
            return response()->json([
                'message' => 'This employee has no biometric data to reset.'
            ], 400);
        }

        // Reset biometric data
        $employee->update([
            'device_user_id' => null,
            'fingerprint_enrolled_at' => null,
        ]);

        return response()->json([
            'message' => 'Biometric fingerprint reset successfully!',
            'employee' => $employee->only(['first_name', 'last_name', 'employee_code'])
        ]);
    }
    private function generateUniqueUid(): int
    {
        do {
            $uid = rand(1000, 9999);
        } while (Employee::where('biometric_uid', $uid)->exists());

        return $uid;
    }
}
