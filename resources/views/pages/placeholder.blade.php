<x-dashboard-layout>
    <div class="rounded-2xl border border-cyan-100 bg-white/80 p-6 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-600 dark:text-cyan-400">
            CyberShield
        </p>

        <h1 class="mt-3 text-2xl font-bold text-slate-950 dark:text-white">
            {{ $title }}
        </h1>

        <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
            {{ $description }}
        </p>
    </div>
</x-dashboard-layout>
