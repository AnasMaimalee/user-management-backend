<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    public function me(Request $request)
    {
        $user = $request->user()->load('employee');

        $profileImageUrl = null;

        if ($user->employee && $user->employee->profile_image) {
            $profileImageUrl = asset('storage/' . $user->employee->profile_image);
        }


        // Attach full image URL (IMPORTANT)
        $user->profile_image_url = $user->profile_image
            ? asset('storage/' . $user->profile_image)
            : null;

        $menus = [];

        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            $menus = [
                ['title' => 'Departments', 'route' => '/departments', 'icon' => 'HomeOutlined'],
                ['title' => 'Employees', 'route' => '/employees', 'icon' => 'UserOutlined'],
                ['title' => 'Branches', 'route' => '/branches', 'icon' => 'MapPinOutlined'],
                ['title' => 'Ranks', 'route' => '/ranks', 'icon' => 'AcademicCapOutlined'],
                ['title' => 'Leaves', 'route' => '/leaves', 'icon' => 'UserOutlined'],
                ['title' => 'Settings', 'route' => '/setting/profile', 'icon' => 'SettingOutlined'],
            ];
        }

        if ($user->hasRole('staff')) {
            $menus = [
                ['title' => 'My Tasks', 'route' => '/tasks', 'icon' => 'CheckCircleOutlined'],
                ['title' => 'Leaves', 'route' => '/leaves/my', 'icon' => 'UserOutlined'],
            ];
        }

        return response()->json([
            'user' => $user,
            'menus' => $menus,
            'roles' => $user->roles()->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }


}
