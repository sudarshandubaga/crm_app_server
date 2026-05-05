<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (public signup).
     */
    public function publicRegister(Request $request)
    {
        $data = $request->validate([
            'firm_name' => 'required|string|max:100',
            'category' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'mobile' => 'nullable|string|max:15',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $firm = \App\Models\Firm::create([
            'name' => $data['firm_name'],
            'category' => $data['category'],
            'logo' => $logoPath,
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => $data['mobile'] ?? null,
            'role' => 'admin',
            'firm_id' => $firm->id,
            'is_active' => true,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['message' => 'User created successfully.', 'user' => $user, 'token' => $token], 201);
    }

    /**
     * Register a new user (admin creates staff).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:15',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|in:admin,staff',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? '',
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'staff',
            'firm_id' => $request->user()->firm_id,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'User created successfully.', 'user' => $user], 201);
    }

    /**
     * Login and return Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Your account is inactive. Please contact admin.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        return response()->json(['user' => $request->user()->load('firm')]);
    }

    /**
     * Logout / revoke current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'mobile'      => 'nullable|string|max:15',
        ]);

        $user->update($data);

        return response()->json(['message' => 'Profile updated.', 'user' => $user]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password does not match.'], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * Forgot password logic.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        // Mock sending an email
        return response()->json(['message' => 'Password reset link sent to your email.']);
    }
}
