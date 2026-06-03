<x-dashboard-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-600 dark:text-cyan-400">Account</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Profile</h1>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-500">Change your name, email, password, and two-factor security.</p>
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-cyan-100 bg-white/80 px-4 py-3 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-[#07111f] dark:shadow-none">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 text-sm font-black text-cyan-700 dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-300">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-black text-slate-950 dark:text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs font-semibold text-slate-500">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-700 dark:text-emerald-200">
                Profile updated.
            </div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-700 dark:text-emerald-200">
                Password updated.
            </div>
        @endif

        @if (in_array(session('status'), ['two-factor-authentication-enabled', 'two-factor-authentication-confirmed', 'two-factor-authentication-disabled', 'recovery-codes-generated'], true))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-700 dark:text-emerald-200">
                Two-factor authentication updated.
            </div>
        @endif

        <section class="rounded-xl border border-cyan-100 bg-white/80 p-5 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-[#07111f] dark:shadow-none">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Profile</p>
                <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">Name and Email</h2>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="text-xs font-bold text-slate-500 dark:text-slate-400">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200"
                        >
                        @error('name')
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="text-xs font-bold text-slate-500 dark:text-slate-400">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="username"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200"
                        >
                        @error('email')
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-4 text-xs font-black text-white transition hover:bg-cyan-500">
                        Save Profile
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-xl border border-cyan-100 bg-white/80 p-5 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-[#07111f] dark:shadow-none">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Password</p>
                <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">Change Password</h2>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="text-xs font-bold text-slate-500 dark:text-slate-400">Current Password</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200"
                    >
                    @foreach ($errors->updatePassword->get('current_password') as $message)
                        <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="password" class="text-xs font-bold text-slate-500 dark:text-slate-400">New Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200"
                        >
                        @foreach ($errors->updatePassword->get('password') as $message)
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs font-bold text-slate-500 dark:text-slate-400">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="mt-2 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200"
                        >
                        @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-4 text-xs font-black text-white transition hover:bg-cyan-500">
                        Save Password
                    </button>
                </div>
            </form>
        </section>

        @php
            $twoFactorEnabled = ! empty($user->two_factor_secret);
            $twoFactorConfirmed = ! empty($user->two_factor_confirmed_at);
            $recoveryCodes = $twoFactorEnabled && ! empty($user->two_factor_recovery_codes) ? $user->recoveryCodes() : [];
        @endphp

        <section class="rounded-xl border border-cyan-100 bg-white/80 p-5 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-[#07111f] dark:shadow-none">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security</p>
                    <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">Two-Factor Authentication</h2>
                    <p class="mt-2 max-w-2xl text-sm font-medium text-slate-500 dark:text-slate-400">
                        Protect your account with a one-time code from an authenticator app.
                    </p>
                </div>

                <span class="inline-flex h-8 items-center rounded-full border px-3 text-[10px] font-black uppercase tracking-[0.16em] {{ $twoFactorConfirmed ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-200' : ($twoFactorEnabled ? 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-200' : 'border-slate-200 bg-slate-50 text-slate-500 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400') }}">
                    {{ $twoFactorConfirmed ? 'Enabled' : ($twoFactorEnabled ? 'Pending' : 'Disabled') }}
                </span>
            </div>

            @if (! $twoFactorEnabled)
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-[#020617]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900 dark:text-white">Add a second verification step</p>
                            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">You will be asked to confirm your password before setup.</p>
                        </div>
                        <form method="POST" action="{{ route('two-factor.enable') }}">
                            @csrf
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-4 text-xs font-black text-white transition hover:bg-cyan-500">
                                Enable 2FA
                            </button>
                        </form>
                    </div>
                </div>
            @else
                @if (! $twoFactorConfirmed)
                    <div class="mt-5 grid gap-5 lg:grid-cols-[240px_1fr]">
                        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#020617]">
                            <div class="mx-auto flex h-48 w-48 items-center justify-center rounded-lg bg-white p-3">
                                {!! $user->twoFactorQrCodeSvg() !!}
                            </div>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-400/20 dark:bg-amber-400/10">
                            <p class="text-sm font-black text-amber-900 dark:text-amber-100">Finish setup</p>
                            <p class="mt-1 text-sm font-medium text-amber-800/80 dark:text-amber-100/80">
                                Scan the QR code in your authenticator app, then enter the 6-digit code.
                            </p>

                            <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                                @csrf
                                <input
                                    name="code"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    placeholder="000000"
                                    class="h-11 w-full rounded-lg border border-amber-200 bg-white px-3 text-sm font-black tracking-[0.25em] text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 dark:border-slate-800 dark:bg-[#020617] dark:text-slate-200 sm:max-w-[180px]"
                                >
                                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-cyan-600 px-4 text-xs font-black text-white transition hover:bg-cyan-500">
                                    Confirm 2FA
                                </button>
                            </form>
                            @error('code')
                                <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-[#020617]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900 dark:text-white">Recovery Codes</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Store these codes somewhere secure.</p>
                            </div>
                            <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                                @csrf
                                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg border border-cyan-200 px-3 text-[11px] font-black text-cyan-700 transition hover:bg-cyan-50 dark:border-cyan-400/20 dark:text-cyan-300 dark:hover:bg-cyan-400/10">
                                    Regenerate
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @forelse ($recoveryCodes as $code)
                                <code class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 dark:border-slate-800 dark:bg-[#07111f] dark:text-slate-200">{{ $code }}</code>
                            @empty
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Recovery codes will appear after setup.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-400/20 dark:bg-red-400/10">
                        <p class="text-sm font-black text-red-900 dark:text-red-100">Disable 2FA</p>
                        <p class="mt-1 text-sm font-medium text-red-800/80 dark:text-red-100/80">
                            Removing 2FA lowers account protection. You may be asked to confirm your password.
                        </p>
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-xs font-black text-white transition hover:bg-red-500">
                                Disable 2FA
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-dashboard-layout>
