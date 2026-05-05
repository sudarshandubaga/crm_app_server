<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('firm_id', $request->user()->firm_id)
            ->latest()
            ->get();

        return response()->json(['users' => $users]);
    }

    public function show(Request $request, User $user)
    {
        $this->authorizeUser($request, $user);

        return response()->json(['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUser($request, $user);

        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'sometimes|string|max:100',
            'mobile'      => 'nullable|string|max:15',
            'is_active'   => 'boolean',
            'role'        => 'sometimes|in:admin,staff',
            'password'    => 'nullable|string|min:6|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'User updated.', 'user' => $user]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeUser($request, $user);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    private function authorizeUser(Request $request, User $user): void
    {
        if ($user->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
