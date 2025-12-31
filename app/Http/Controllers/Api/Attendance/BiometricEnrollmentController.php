<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\BiometricDevice;
use Illuminate\Http\Request;
use Jmrashed\Zkteco\Lib\ZKTeco;
use Illuminate\Http\JsonResponse;

class BiometricEnrollmentController extends Controller
{
    public function enroll(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Find active device — but don't fail hard if none
        $device = BiometricDevice::where('is_active', true)->first();

        if (!$device) {
            return response()->json([
                'message' => 'No active biometric device found. Please configure and activate a device in settings.'
            ], 400);
        }

        try {
            $zk = new ZKTeco($device->ip, $device->port);

            if (!$zk->connect()) {
                return response()->json([
                    'message' => 'Failed to connect to biometric device. Check IP, port, or device power.'
                ], 500);
            }

            // Generate UID if not exists
            $uid = $employee->biometric_uid ?? $this->generateUniqueUid();

            // Send user to device
            $zk->setUser(
                uid: $uid,
                userid: $employee->employee_code,
                name: $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name,
                password: '',
                role: 0 // normal user
            );

            // Mark as sent to device (even if fingerprint not yet scanned)
            $employee->update([
                'biometric_uid' => $uid,
                'fingerprint_enrolled_at' => null, // will be set when actual fingerprint is scanned
            ]);

            $zk->disconnect();

            return response()->json([
                'message' => 'Employee successfully sent to biometric device.',
                'instructions' => 'Ask the employee to place their finger on the device to complete fingerprint enrollment.',
                'uid' => $uid,
                'employee' => $employee->only(['id', 'first_name', 'last_name', 'employee_code'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Enrollment failed due to device communication error.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateUniqueUid(): int
    {
        do {
            $uid = rand(1000, 9999);
        } while (Employee::where('biometric_uid', $uid)->exists());

        return $uid;
    }
}
