<x-dashboard-layout>
    <div class="mx-auto max-w-3xl space-y-6">
        {{-- HEADER --}}
        <div class="flex items-start gap-4">
            <a href="{{ route('clients.index') }}"
               class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-[#07111f] text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-300"
               title="Back to clients"
               aria-label="Back to clients">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/>
                </svg>
            </a>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-cyan-400">Clients</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Create Client</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Add a customer account before attaching projects and protected assets.
                </p>
            </div>
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-6 rounded-2xl border border-cyan-400/10 bg-[#07111f] p-6 shadow-2xl shadow-black/20">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                {{-- COMPANY --}}
                <label class="space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Company Name</span>
                    <input
                        name="company_name"
                        type="text"
                        value="{{ old('company_name') }}"
                        required
                        placeholder="Acme Security"
                        class="h-11 w-full rounded-xl border border-slate-700 bg-[#020617] px-4 text-sm font-medium text-slate-300 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">
                    @error('company_name')
                        <span class="block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                {{-- PHONE --}}
                <label class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Phone</span>
                    <input
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        placeholder="+212 600 000 000"
                        class="h-11 w-full rounded-xl border border-slate-700 bg-[#020617] px-4 text-sm font-medium text-slate-300 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">
                    @error('phone')
                        <span class="block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                {{-- STATUS --}}
                <label class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Status</span>
                    <select
                        name="status"
                        class="h-11 w-full rounded-xl border border-slate-700 bg-[#020617] px-4 text-sm font-bold text-slate-300 outline-none transition focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="warning" @selected(old('status') === 'warning')>Warning</option>
                        <option value="critical" @selected(old('status') === 'critical')>Critical</option>
                    </select>
                    @error('status')
                        <span class="block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                {{-- ADDRESS --}}
                <label class="space-y-2 md:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Address</span>
                    <textarea
                        name="address"
                        rows="4"
                        placeholder="City, country, office or billing address"
                        class="w-full resize-none rounded-xl border border-slate-700 bg-[#020617] px-4 py-3 text-sm font-medium text-slate-300 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/40 focus:ring-2 focus:ring-cyan-400/10">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-medium text-cyan-300">
                New clients can be linked to projects from the Add Project page.
            </div>

            {{-- ACTIONS --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('clients.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-700 px-4 text-sm font-bold text-slate-400 transition hover:bg-slate-800 hover:text-slate-200">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="h-11 rounded-xl border border-cyan-400/30 bg-cyan-400/10 px-5 text-sm font-bold text-cyan-300 transition hover:bg-cyan-400/20">
                    Create Client
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
