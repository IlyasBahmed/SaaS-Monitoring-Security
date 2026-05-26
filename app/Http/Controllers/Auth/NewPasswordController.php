<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\clients;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $setupUser = filled($request->email)
            ? User::query()->where('email', $request->email)->first()
            : null;

        $setupClient = $setupUser
            ? clients::query()->where('user_id', $setupUser->id)->first()
            : null;

        return view('auth.reset-password', [
            'request' => $request,
            'setupUser' => $setupUser,
            'setupClient' => $setupClient,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $resetUser = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request, &$resetUser) {
                $role = strtolower(trim((string) $user->role));
                $currentStatus = strtolower(trim((string) $user->status));
                $isInitialSetup = $currentStatus === 'pending';
                $statusAfterSetup = $user->status;

                if ($isInitialSetup && in_array($role, ['super admin', 'soc analyst'], true)) {
                    $statusAfterSetup = 'Active';
                }

                if ($isInitialSetup && $role === 'client') {
                    $statusAfterSetup = 'active';
                }

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'status' => $statusAfterSetup,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                $resetUser = $user;
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        if ($resetUser && in_array(strtolower(trim((string) $resetUser->role)), ['client', 'super admin', 'soc analyst'], true)) {
            Auth::login($resetUser);
            $request->session()->regenerate();

            if (in_array(strtolower(trim((string) $resetUser->role)), ['super admin', 'soc analyst'], true)) {
                return redirect()->route('dashboard')->with('success', 'Password defined successfully.');
            }

            return redirect()->route('client.dashboard')->with('success', 'Password defined successfully.');
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
