<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification | CyberShield</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background-color: #020617; }
        .auth-panel {
            background: rgba(2, 6, 23, 0.82);
            border: 1px solid rgba(6, 182, 212, 0.18);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(18px);
        }
        .input-dark {
            background-color: rgba(2, 6, 23, 0.75);
            border: 1px solid #1e293b;
        }
        .input-dark:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.22);
            outline: none;
        }
        .cyber-button {
            background: linear-gradient(95deg, #06b6d4, #2dd4bf);
        }
    </style>
</head>
<body class="antialiased">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#020617] px-5 py-10 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_20%_20%,#06b6d41a,transparent_55%),radial-gradient(ellipse_at_85%_75%,#2563eb1f,transparent_55%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(34,211,238,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(34,211,238,0.035)_1px,transparent_1px)] bg-[size:42px_42px]"></div>

        <section class="auth-panel relative w-full max-w-md rounded-3xl p-7 md:p-9">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-cyan-400/40 bg-cyan-400/10 text-cyan-300 shadow-lg shadow-cyan-500/15">
                    <i class="fas fa-mobile-screen-button text-3xl"></i>
                </div>
                <p class="mt-5 text-[11px] font-mono uppercase tracking-[0.28em] text-cyan-300">Secure checkpoint</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">Two-factor verification</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">Enter the code from your authenticator app, or use one recovery code.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-400/25 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5">
                @csrf

                <div id="authenticator-code-field">
                    <label for="code" class="text-sm font-bold text-slate-200">
                        <i class="fas fa-key text-cyan-300"></i> Authenticator code
                    </label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        autofocus
                        class="input-dark mt-2 h-12 w-full rounded-xl px-4 text-center text-lg font-black tracking-[0.35em] text-white placeholder:text-slate-600"
                        placeholder="000000"
                    >
                </div>

                <div id="recovery-code-field" class="hidden">
                    <label for="recovery_code" class="text-sm font-bold text-slate-200">
                        <i class="fas fa-life-ring text-cyan-300"></i> Recovery code
                    </label>
                    <input
                        id="recovery_code"
                        name="recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        class="input-dark mt-2 h-12 w-full rounded-xl px-4 text-sm font-bold text-white placeholder:text-slate-600"
                        placeholder="Enter recovery code"
                    >
                </div>

                <button type="button" id="toggle-recovery" class="text-sm font-bold text-cyan-300 transition hover:text-cyan-200">
                    Use a recovery code
                </button>

                <button type="submit" class="cyber-button flex h-12 w-full items-center justify-center gap-2 rounded-xl text-sm font-black text-slate-950 shadow-lg shadow-cyan-500/25 transition hover:translate-y-[-1px]">
                    <i class="fas fa-shield-alt"></i>
                    Verify and continue
                </button>
            </form>
        </section>
    </main>

    <script>
        (function () {
            const toggle = document.getElementById('toggle-recovery');
            const codeField = document.getElementById('authenticator-code-field');
            const recoveryField = document.getElementById('recovery-code-field');
            const codeInput = document.getElementById('code');
            const recoveryInput = document.getElementById('recovery_code');

            toggle.addEventListener('click', function () {
                const usingRecovery = recoveryField.classList.toggle('hidden') === false;
                codeField.classList.toggle('hidden', usingRecovery);
                toggle.textContent = usingRecovery ? 'Use an authenticator code' : 'Use a recovery code';
                codeInput.value = '';
                recoveryInput.value = '';
                (usingRecovery ? recoveryInput : codeInput).focus();
            });
        })();
    </script>
</body>
</html>
