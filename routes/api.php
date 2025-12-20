<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Validation\ValidationException;

Route::post('/register', function(Request $request){
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);

    $user = User::query()->create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json($user);
});

//Route::post('/login', function(Request $request){
//    $request->validate([
//        'email' => 'required|email',
//        'password' => 'required|min:8',
//    ]);
//
//    $user = User::query()->where('email', $request->email)->first();
//    if (!$user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)){
//        throw ValidationException::withMessages([
//            'email' => ['The provided credentials are incorrect.'],
//        ]);
//    }
//
//    $token = $user->createToken('api-token')->plainTextToken;
//
//    return response()->json([
//        'user' => $user,
//        'token' => $token,
//    ]);
//
//});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/dashboard', function (Request $request) {
    return response()->json([
        'message' => 'Welcome to your Dashboard!',
        'user' => $request->user()
    ]);
});


