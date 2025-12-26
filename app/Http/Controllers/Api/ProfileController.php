<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->load('employee'));
    }
    public function update(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Employee profile not found'
            ], 422);
        }

        $validated = $request->validate([
            'profile_image' => 'nullable|image|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        /* PASSWORD */
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }

            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        /* PROFILE IMAGE */
        if ($request->hasFile('profile_image')) {

            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }

            $employee->profile_image = $request->file('profile_image')
                ->store('profiles', 'public');

            $employee->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('employee')
        ]);
    }

}
