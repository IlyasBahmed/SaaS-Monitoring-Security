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
    x-data="{ darkMode: document.documentElement.classList.contains('dark'), sidebarOpen: false }"
    x-init="$watch('darkMode', value => {
        document.documentElement.classList.toggle('dark', value);
        localStorage.setItem('theme', value ? 'dark' : 'light');
    })"
    @keydown.escape.window="sidebarOpen = false"
    class="overflow-x-hidden bg-slate-100 text-slate-950 transition-colors duration-300 dark:bg-[#020617] dark:text-white"
>
    <div class="min-h-screen flex">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"
            aria-hidden="true"
        ></div>

        <x-sidebar />

        <main class="min-w-0 flex-1 overflow-y-auto bg-slate-50/70 p-3 text-slate-950 sm:p-4 md:p-6 dark:bg-transparent dark:text-white">
            <div class="mx-auto max-w-[1600px]">
                <x-topbar />
                {{ $slot }}
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
