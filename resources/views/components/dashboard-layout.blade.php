<!DOCTYPE html>
<html lang="en">
<head>
@stack('styles')
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CyberShield</title>
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
    x-init="$watch('darkMode', value => {
        document.documentElement.classList.toggle('dark', value);
        localStorage.setItem('theme', value ? 'dark' : 'light');
    })"
    class="bg-slate-100 text-slate-950 transition-colors duration-300 dark:bg-[#020617] dark:text-white"
>
    <div class="min-h-screen flex">
        <x-sidebar />

        <main class="min-w-0 flex-1 overflow-y-auto bg-slate-50/70 p-4 text-slate-950 md:p-6 dark:bg-transparent dark:text-white">
            <div class="mx-auto max-w-[1600px]">
                <x-topbar />
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
