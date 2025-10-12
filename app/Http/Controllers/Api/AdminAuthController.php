<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $admin->is_active) {
            return failure('Account is deactivated.');
        }

        $token = $admin->createToken('admin-auth', ['admin:*'])->plainTextToken;

        return success('Login successful', ['admin' => $admin, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success('Logged out');
    }

    public function show(Request $request)
    {
        $admin = $request->user(); // sometimes null
        if (! $admin) {
            $admin = auth('sanctum')->user(); // fallback
        }

        return success('Admin Profile', $admin);
    }

    public function update(Request $request)
    {
        $admin = $request->user();

        if (! $admin) {
            return error('Unauthenticated', 401);
        }
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:3',
                'phone' => 'nullable|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return failure($e->errors());
        }

        $admin->update($validated);

        return success('Profile updated successfully', $admin->fresh());
    }

    public function changePassword(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return failure('Current password is incorrect');
        }

        $admin->update(['password' => Hash::make($validated['new_password'])]);

        return success('Password updated successfully');
    }
}
