<x-dashboard-layout>
    @php
        $typeLabels = [
            'project_security' => 'Project security report',
            'incident_summary' => 'Incident summary',
            'vulnerability_summary' => 'Vulnerability summary',
            'cloudflare_coverage' => 'Cloudflare coverage',
        ];
        $periodLabels = [
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days',
            'this_month' => 'This month',
            'last_quarter' => 'Last quarter',
        ];
        $statusClass = static function (string $status): string {
            return [
                'pending' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'in_progress' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                'ready' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                'failed' => 'border-red-400/20 bg-red-400/10 text-red-300',
            ][strtolower($status)] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-5 py-4 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 px-5 py-4 text-sm font-bold text-red-300">
                {{ session('error') ?: $errors->first() }}
            </div>
        @endif

        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Reports</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $client?->company_name ?? 'Client reports' }}</h1>
                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                        Request a report for one of your projects and download the generated PDF.
                    </p>
                </div>
                <span class="inline-flex h-10 items-center rounded-lg border border-slate-700 bg-slate-950/40 px-4 text-xs font-black text-slate-400">
                    {{ $reportRequests->count() }} requests
                </span>
            </div>
        </section>

        @if (! $client)
            <section class="rounded-xl border border-amber-400/20 bg-amber-400/10 p-5">
                <h2 class="text-lg font-black text-amber-200">Client profile not linked</h2>
                <p class="mt-2 text-sm font-medium text-amber-100/80">
                    Your user account is active, but no client record is attached yet.
                </p>
            </section>
        @endif

        <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Request</p>
                <h2 class="mt-1 text-lg font-black text-white">New project report</h2>

                <form method="POST" action="{{ route('client.reports.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Project</span>
                        <select name="project_id" required class="h-11 w-full rounded-lg border border-slate-800 bg-[#020617] px-3 text-sm font-bold text-slate-300 outline-none focus:border-cyan-400">
                            <option value="">Choose a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((int) old('project_id') === (int) $project->id)>
                                    {{ $project->domain ?: $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Report Type</span>
                        <select name="type" required class="h-11 w-full rounded-lg border border-slate-800 bg-[#020617] px-3 text-sm font-bold text-slate-300 outline-none focus:border-cyan-400">
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', 'project_security') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Period</span>
                        <select name="period" required class="h-11 w-full rounded-lg border border-slate-800 bg-[#020617] px-3 text-sm font-bold text-slate-300 outline-none focus:border-cyan-400">
                            @foreach ($periodLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('period', 'last_30_days') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Note</span>
                        <textarea name="note" rows="4" maxlength="1000" placeholder="Optional details for the SOC team" class="w-full rounded-lg border border-slate-800 bg-[#020617] px-3 py-3 text-sm font-semibold text-slate-300 outline-none placeholder:text-slate-600 focus:border-cyan-400">{{ old('note') }}</textarea>
                    </label>

                    <button type="submit"
                            @disabled(! $client || $projects->isEmpty())
                            class="h-11 rounded-lg bg-cyan-600 px-5 text-sm font-black text-white transition hover:bg-cyan-500 disabled:cursor-not-allowed disabled:opacity-50">
                        Request Report
                    </button>
                </form>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Downloads</p>
                    <h2 class="mt-1 text-lg font-black text-white">Your report requests</h2>
                </div>

                <div class="divide-y divide-slate-800">
                    @forelse ($reportRequests as $requestItem)
                        @php $status = strtolower((string) $requestItem->status); @endphp
                        <div class="grid gap-4 px-5 py-4 lg:grid-cols-[1fr_auto] lg:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-white">
                                    {{ $requestItem->project?->domain ?: $requestItem->project?->name ?: 'Project' }}
                                </p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $typeLabels[$requestItem->type] ?? ucwords(str_replace('_', ' ', $requestItem->type)) }}
                                    / {{ $periodLabels[$requestItem->period] ?? ucwords(str_replace('_', ' ', $requestItem->period)) }}
                                    / {{ $requestItem->requested_at ? $requestItem->requested_at->diffForHumans() : $requestItem->created_at?->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $statusClass($status) }}">
                                    {{ str_replace('_', ' ', $status) }}
                                </span>
                                @if ($status === 'ready')
                                    <a href="{{ route('client.reports.download', $requestItem) }}"
                                       class="inline-flex h-9 items-center rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-3 text-xs font-black text-emerald-300 transition hover:bg-emerald-400/20">
                                        Download
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                            No report requests yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-dashboard-layout>
