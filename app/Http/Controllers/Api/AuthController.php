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

    $roles = $user->getRoleNames();
    $permissions = $user->getAllPermissions()->pluck('name');

    $menus = [];

    if ($user->hasRole(['super_admin', 'admin'])) {
        $menus = [
            ['title' => 'Wallet', 'route' => '/wallet/my'],
            ['title' => 'Departments', 'route' => '/departments'],
            ['title' => 'Employees', 'route' => '/employees'],
            ['title' => 'Branches', 'route' => '/branches'],
            ['title' => 'Ranks', 'route' => '/ranks'],
            ['title' => 'Leaves', 'route' => '/leaves'],
            ['title' => 'Payroll', 'route' => '/payroll'],
            ['title' => 'Settings', 'route' => '/setting/profile'],
        ];
    }

    if ($user->hasRole('staff')) {
        $menus = [
            ['title' => 'Wallet', 'route' => '/wallet/my'],
            ['title' => 'Leaves', 'route' => '/leaves/my'],
            ['title' => 'Payroll', 'route' => '/payroll/my'],
            ['title' => 'Profile', 'route' => '/setting/profile'],
        ];
    }

    return response()->json([
        'user' => $user,
        'employee' => $user->employee,
        'menus' => $menus,
        'roles' => $roles,
        'permissions' => $permissions,
    ]);
}

}
