<?php

namespace App\Http\Controllers;
use App\Notifications\InviteUserNotification;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;


class UserInviteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:Super Admin,SOC Analyst'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => 'pending',
            'password' => bcrypt(Str::random(32)),
        ]);

       $token = Password::createToken($user);

$user->notify(new InviteUserNotification($token));

        return back()->with('success', 'Invitation sent successfully.');
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validateWithBag('editUser', [
            'user_id' => ['required', Rule::in([(string) $user->id])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:Super Admin,SOC Analyst'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Member updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors([
                'delete' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return back()->with('success', 'Member deleted successfully.');
    }
}
