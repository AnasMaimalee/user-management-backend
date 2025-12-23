<?php

// app/Http/Controllers/EmployeeInvitationController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Mail\EmployeeInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeeInvitationController extends Controller
{
    // Show the Vue set-password page
    public function show(Request $request, Employee $employee)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link.');
        }

        if ($employee->user) {
            return redirect('/login')->with('error', 'You already have an account.');
        }

        // Return the main Vue app – it will handle routing to your SetPassword page
        return view('app'); // your main blade with <div id="app"></div>
    }

    // API endpoint called from Vue
    // app/Http/Controllers/EmployeeInvitationController.php

    public function store(Request $request, Employee $employee)
    {
        // Check signature from query params
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired invitation link.'], 401);
        }

        if ($employee->user) {
            return response()->json(['message' => 'Account already exists.'], 400);
        }

        $validated = $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        User::create([
            'id' => (string) Str::uuid(),
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'employee_id' => $employee->id,
        ]);

        return response()->json(['message' => 'Account created successfully! You can now log in.']);
    }

    // Send invitation (called from employee management page)
    public function sendInvitation(Employee $employee)
    {
        if (!$employee->email) {
            return response()->json(['message' => 'Employee has no email'], 400);
        }

        if ($employee->user) {
            return response()->json(['message' => 'Employee already has an account'], 400);
        }

        Mail::to($employee->email)->send(new EmployeeInvitationMail($employee));

        return response()->json(['message' => 'Invitation sent successfully']);
    }
}
