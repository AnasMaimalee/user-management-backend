<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function me()
    {
        $user = auth()->user();

        $userRoles = $user->roles->pluck('name')->toArray();

        // Define menus for different roles
        $menus = [];

        // Common menus for everyone

        if(in_array('staff', $userRoles)) {
            $menus[] = ['title' => 'My Wallet', 'route' => '/wallet/my', 'icon' => 'WalletOutlined'];
            $menus[] = ['title' => 'Loans', 'route' => '/loans/my', 'icon' => 'DollarOutlined'];
            $menus[] = ['title' => 'Leaves', 'route' => '/leaves/my', 'icon' => 'DollarOutlined'];

            $menus[] = ['title' => 'Job Ranks', 'route' => '/ranks', 'icon' => 'ProfileOutlined'];
            $menus[] = ['title' => 'Branches', 'route' => '/branches', 'icon' => 'BankOutlined'];
            $menus[] = ['title' => 'Departments', 'route' => '/departments', 'icon' => 'ApartmentOutlined'];

            //$menus[] = ['title' => 'Payroll', 'route' => '/payroll', 'icon' => 'DollarOutlined'];
            //$menus[] = ['title' => 'Loan Requests', 'route' => '/admin/loans', 'icon' => 'MoneyCollectOutlined'];

        }

        // Admin & HR only menus
        if (in_array('super_admin', $userRoles) || in_array('hr', $userRoles)) {
            $menus[] = ['title' => 'Employees', 'route' => '/employees', 'icon' => 'UsergroupAddOutlined'];
            $menus[] = ['title' => 'Departments', 'route' => '/departments', 'icon' => 'ApartmentOutlined'];
            $menus[] = ['title' => 'Branches', 'route' => '/branches', 'icon' => 'BankOutlined'];
            $menus[] = ['title' => 'Job Ranks', 'route' => '/ranks', 'icon' => 'ProfileOutlined'];
            $menus[] = ['title' => 'Payroll', 'route' => '/payroll', 'icon' => 'DollarOutlined'];
            $menus[] = ['title' => 'Loan Requests', 'route' => '/admin/loans', 'icon' => 'MoneyCollectOutlined'];
            $menus[] = ['title' => 'Withdrawal Requests', 'route' => '/admin/withdrawals', 'icon' => 'WalletOutlined'];
            $menus[] = ['title' => 'Leave Request', 'route' => '/admin/leave-request', 'icon' => 'FieldTimeOutlined'];

        }

        $menus[] = ['title' => 'Company Chat', 'route' => '/chat', 'icon' => 'MessageOutlined'];
        $menus[] = ['title' => 'Profile', 'route' => '/setting/profile', 'icon' => 'UserOutlined'];


        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $userRoles,
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'menus' => $menus,
        ]);
    }
}
