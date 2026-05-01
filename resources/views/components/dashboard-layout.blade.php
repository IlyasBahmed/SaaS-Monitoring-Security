<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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

        <main class="flex-1 overflow-hidden p-6">
            <x-topbar />
            {{ $slot }}
        </main>
    </div>
</body>
</html>
