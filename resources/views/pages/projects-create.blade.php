<x-dashboard-layout>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-start gap-4">
        <a href="{{ route('projects.index') }}"
           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-[#07111f] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300"
           title="Back to projects"
           aria-label="Back to projects">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/>
            </svg>
        </a>

        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-400">Projects</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Create Project</h1>
            <p class="text-sm text-slate-500 mt-1">
                Configure a new protected asset and generate its agent API key
            </p>
        </div>
    </div>

    {{-- FORM --}}
    <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- PROJECT NAME --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">Project Name</label>
                <input type="text" name="name" required
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
            </div>

            {{-- DOMAIN --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">Domain</label>
                <input type="text" name="domain" required placeholder="example.com"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
            </div>

            {{-- IP ADDRESS --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">IP Address</label>
                <input type="text" name="ip_address" placeholder="192.168.1.1"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
            </div>

            {{-- CLIENT --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">Client</label>
                <select name="client_id" required
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">
                            {{ $client->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- AGENT TYPE --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">Agent Type</label>
                <select name="agent_type" required
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    <option value="Web App">Web App</option>
                    <option value="WordPress">WordPress</option>
                    <option value="Server">Server</option>
                </select>
            </div>

            {{-- STATUS --}}
            <div>
                <label class="text-xs text-slate-400 font-bold">Initial Status</label>
                <select name="status"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    <option value="offline">Offline</option>
                    <option value="active">Active</option>
                    <option value="warning">Warning</option>
                </select>
            </div>

        </div>

        {{-- INFO BOX --}}
        <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 p-4 text-sm text-cyan-300">
            ⚡ An API Key will be automatically generated for this project after creation.
        </div>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3">

            <a href="{{ route('projects.index') }}"
                class="h-10 px-5 flex items-center rounded-xl border border-slate-700 text-slate-400 hover:text-white transition">
                Cancel
            </a>

            <button type="submit"
                class="h-10 px-6 rounded-xl bg-cyan-400/10 border border-cyan-400/30 text-cyan-300 font-bold hover:bg-cyan-400/20 transition">
                Create Project
            </button>

        </div>

    </form>

</div>

</x-dashboard-layout>
