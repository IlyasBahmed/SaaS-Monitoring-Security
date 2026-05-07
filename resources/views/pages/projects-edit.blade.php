<x-dashboard-layout>

<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-start gap-4">
        <a href="{{ route('projects.show', $project) }}"
           class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-[#07111f] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300"
           title="Back to project"
           aria-label="Back to project">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/>
            </svg>
        </a>

        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-400">Projects</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Edit Project</h1>
            <p class="text-sm text-slate-500 mt-1">
                Update project details, ownership, stack, and current status.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-slate-400 font-bold">Project Name</label>
                <input type="text" name="name" required value="{{ old('name', $project->name) }}"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                @error('name')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs text-slate-400 font-bold">Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $project->domain) }}" placeholder="example.com"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                @error('domain')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs text-slate-400 font-bold">IP Address</label>
                <input type="text" name="ip_address" value="{{ old('ip_address', $project->ip_address) }}" placeholder="192.168.1.1"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                @error('ip_address')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs text-slate-400 font-bold">Client</label>
                <select name="client_id" required
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id', $project->client_id) === $client->id)>
                            {{ $client->company_name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs text-slate-400 font-bold">Project Type</label>
                <select name="stack" required
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    @foreach($projectTypes as $projectType)
                        <option value="{{ $projectType }}" @selected(old('stack', \App\Models\Projects::normalizeProjectType($project->stack)) === $projectType)>
                            {{ $projectType }}
                        </option>
                    @endforeach
                </select>
                @error('stack')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs text-slate-400 font-bold">Status</label>
                <select name="status"
                    class="mt-2 w-full h-11 px-4 bg-[#07111f] border border-slate-700 rounded-xl text-slate-300 text-sm outline-none focus:border-cyan-400/40">
                    <option value="offline" @selected(old('status', strtolower($project->status ?? 'offline')) === 'offline')>Offline</option>
                    <option value="active" @selected(old('status', strtolower($project->status ?? 'offline')) === 'active')>Active</option>
                    <option value="warning" @selected(old('status', strtolower($project->status ?? 'offline')) === 'warning')>Warning</option>
                </select>
                @error('status')
                    <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-[#07111f] p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">API Key</p>
            <p class="mt-2 break-all font-mono text-xs font-bold text-cyan-300">
                {{ $project->api_key ? substr($project->api_key, 0, 5).str_repeat('*', 6).substr($project->api_key, -6) : 'No API key' }}
            </p>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('projects.show', $project) }}"
                class="h-10 px-5 flex items-center rounded-xl border border-slate-700 text-slate-400 hover:text-white transition">
                Cancel
            </a>

            <button type="submit"
                class="h-10 px-6 rounded-xl bg-cyan-400/10 border border-cyan-400/30 text-cyan-300 font-bold hover:bg-cyan-400/20 transition">
                Save Changes
            </button>
        </div>
    </form>

</div>

</x-dashboard-layout>
