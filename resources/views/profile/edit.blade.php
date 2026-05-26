<x-dashboard-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-600 dark:text-cyan-400">Account</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Profile</h1>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-500">Change your name, email, and password.</p>
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
    </div>
</x-dashboard-layout>
