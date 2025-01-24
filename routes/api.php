<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    return response()->json(['message' => 'Please login'], 401);
})->name('login');

Route::middleware('web')->group(function () {
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['message' => 'Login successful'], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out'], 200);
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});