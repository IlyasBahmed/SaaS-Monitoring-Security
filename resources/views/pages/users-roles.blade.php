<x-dashboard-layout>
    <div x-data="usersRolesPage()" class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-600 dark:text-cyan-400">
                    Access Control
                </p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">
                    Users & Roles
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                    Manage team access, SOC permissions, and account activity from one focused workspace.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-cyan-100 bg-white px-4 text-sm font-bold text-slate-700 shadow-lg shadow-slate-200/60 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-300 dark:shadow-none dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:text-cyan-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                    </svg>
                    Export
                </button>

                <button type="button"
                        @click="inviteOpen = true"
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-cyan-600 px-4 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 transition hover:bg-cyan-500 dark:bg-cyan-400/15 dark:text-cyan-200 dark:ring-1 dark:ring-cyan-400/30 dark:hover:bg-cyan-400/25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Invite Member
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 dark:border-emerald-400/10 dark:bg-emerald-400/5 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errors->first('delete') ?: 'Please check the form and try again.' }}
            </div>
        @endif

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-cyan-100 bg-white/85 p-5 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Members</p>
                    <span class="rounded-lg bg-cyan-50 p-2 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950 dark:text-white" x-text="users.length"></p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Total platform accounts</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white/85 p-5 shadow-lg shadow-slate-200/60 dark:border-emerald-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Active</p>
                    <span class="rounded-lg bg-emerald-50 p-2 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950 dark:text-white" x-text="activeUsers"></p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Currently enabled users</p>
            </div>

            <div class="rounded-2xl border border-purple-100 bg-white/85 p-5 shadow-lg shadow-slate-200/60 dark:border-purple-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Admins</p>
                    <span class="rounded-lg bg-purple-50 p-2 text-purple-700 dark:bg-purple-400/10 dark:text-purple-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950 dark:text-white" x-text="countByRole('Super Admin')"></p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Privileged accounts</p>
            </div>

            <div class="rounded-2xl border border-cyan-100 bg-white/85 p-5 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Analysts</p>
                    <span class="rounded-lg bg-cyan-50 p-2 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M8 4h8l2 2v14H6V6l2-2z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950 dark:text-white" x-text="countByRole('SOC Analyst')"></p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">SOC analyst accounts</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="rounded-2xl border border-cyan-100 bg-white/85 p-4 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="relative w-full xl:max-w-md">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                    </svg>
                    <input
                        x-model="search"
                        type="search"
                        placeholder="Search users, email, or access..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-cyan-300/50 dark:focus:bg-[#07111f]"
                    >
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select
                        x-model="role"
                        class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-300 dark:focus:border-cyan-300/50">
                        <option value="all">All roles</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="SOC Analyst">SOC Analyst</option>
                    </select>

                    <div class="grid grid-cols-3 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-cyan-400/10 dark:bg-[#020617]">
                        <button type="button"
                                @click="status = 'all'"
                                class="rounded-lg px-3 py-2 text-xs font-bold transition"
                                :class="status === 'all' ? 'bg-white text-cyan-700 shadow-sm dark:bg-cyan-400/10 dark:text-cyan-200' : 'text-slate-500 hover:text-slate-900 dark:text-slate-500 dark:hover:text-slate-200'">
                            All
                        </button>
                        <button type="button"
                                @click="status = 'Active'"
                                class="rounded-lg px-3 py-2 text-xs font-bold transition"
                                :class="status === 'Active' ? 'bg-white text-emerald-700 shadow-sm dark:bg-emerald-400/10 dark:text-emerald-300' : 'text-slate-500 hover:text-slate-900 dark:text-slate-500 dark:hover:text-slate-200'">
                            Active
                        </button>
                        <button type="button"
                                @click="status = 'Inactive'"
                                class="rounded-lg px-3 py-2 text-xs font-bold transition"
                                :class="status === 'Inactive' ? 'bg-white text-slate-800 shadow-sm dark:bg-slate-800 dark:text-slate-200' : 'text-slate-500 hover:text-slate-900 dark:text-slate-500 dark:hover:text-slate-200'">
                            Inactive
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-cyan-100 bg-white/90 shadow-lg shadow-slate-200/60 dark:border-cyan-400/10 dark:bg-slate-950/60 dark:shadow-black/20">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-cyan-400/10">
                <div>
                    <h2 class="text-sm font-black text-slate-950 dark:text-white">Team Members</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-500">
                        <span x-text="filteredUsers.length"></span> visible accounts
                    </p>
                </div>

                <button type="button"
                        @click="clearFilters()"
                        class="rounded-lg px-3 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                    Clear filters
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px]">
                    <thead class="bg-slate-50/80 dark:bg-white/[0.02]">
                        <tr class="text-left text-[11px] uppercase tracking-[0.18em] text-slate-400 dark:text-cyan-500/70">
                            <th class="px-5 py-4 font-black">User</th>
                            <th class="px-5 py-4 font-black">Role</th>
                            <th class="px-5 py-4 font-black">Access</th>
                            <th class="px-5 py-4 font-black">Status</th>
                            <th class="px-5 py-4 font-black">Last Active</th>
                            <th class="px-5 py-4 text-right font-black">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-cyan-400/10">
                        <template x-for="user in filteredUsers" :key="user.email">
                            <tr class="transition hover:bg-cyan-50/60 dark:hover:bg-white/[0.025]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 text-xs font-black text-cyan-700 shadow-sm dark:border-cyan-400/30 dark:bg-cyan-400/10 dark:text-cyan-300">
                                            <span x-text="initials(user.name)"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-950 dark:text-white" x-text="user.name"></p>
                                            <p class="truncate text-xs text-slate-500 dark:text-slate-500" x-text="user.email"></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 ring-inset" :class="roleBadge(user.role)" x-text="user.role"></span>
                                </td>

                                <td class="px-5 py-4 text-sm font-medium text-slate-600 dark:text-slate-400" x-text="user.access"></td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-lg px-2.5 py-1 text-xs font-bold ring-1 ring-inset"
                                          :class="statusLabel(user.status) === 'Active'
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20'
                                            : 'bg-slate-100 text-slate-500 ring-slate-200 dark:bg-slate-800/70 dark:text-slate-400 dark:ring-slate-700'">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusLabel(user.status) === 'Active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        <span x-text="statusLabel(user.status)"></span>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm font-medium text-slate-500 dark:text-slate-500" x-text="user.last_login_at ?? 'Never'"></td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                                @click="openEdit(user)"
                                                aria-label="Edit user"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/10 dark:hover:text-cyan-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                            </svg>
                                        </button>
                                        <button type="button"
                                                @click="openDelete(user)"
                                                aria-label="Delete user"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-500 transition hover:bg-red-50 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-500/10">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="filteredUsers.length === 0" x-cloak class="border-t border-slate-100 px-6 py-12 text-center dark:border-cyan-400/10">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-black text-slate-950 dark:text-white">No users found</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try another search term or reset the filters.</p>
            </div>
        </div>

        {{-- Role Profiles --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <template x-for="item in roles" :key="item.name">
                <div class="rounded-2xl border p-5 shadow-lg shadow-slate-200/50 dark:shadow-none" :class="roleCard(item.tone)">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-black text-slate-950 dark:text-white" x-text="item.name"></p>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400" x-text="item.description"></p>
                        </div>
                        <span class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-white px-2 text-sm font-black text-slate-700 shadow-sm dark:bg-slate-950/70 dark:text-slate-200" x-text="countByRole(item.name)"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Delete Member Confirmation --}}
        <div x-show="deleteOpen"
             x-cloak
             @keydown.escape.window="closeDelete()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="deleteOpen"
                 x-transition.opacity
                 @click="closeDelete()"
                 class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm dark:bg-black/70"></div>

            <div x-show="deleteOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                 x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                 x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                 class="relative w-full max-w-md overflow-hidden rounded-2xl border border-red-100 bg-white shadow-2xl shadow-slate-300/70 dark:border-red-500/20 dark:bg-[#020617] dark:shadow-red-950/20">

                <div class="absolute inset-x-0 top-0 h-1 bg-red-500"></div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <path stroke-linecap="round" d="M12 9v4"/>
                                <path stroke-linecap="round" d="M12 17h.01"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-red-500">
                                Confirm Delete
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                                Delete this member?
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                This will permanently remove
                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="deleteUser.name || 'this member'"></span>
                                from Users & Roles.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 dark:border-red-500/20 dark:bg-red-500/10">
                        <p class="truncate text-sm font-bold text-red-700 dark:text-red-300" x-text="deleteUser.email"></p>
                        <p class="mt-1 text-xs font-semibold text-red-500 dark:text-red-400" x-text="deleteUser.role"></p>
                    </div>

                    <form method="POST" :action="deleteAction(deleteUser)" class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-red-500/20 sm:flex-row sm:justify-end">
                        @csrf
                        @method('DELETE')

                        <button type="button"
                                @click="closeDelete()"
                                class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                            Cancel
                        </button>

                        <button type="submit"
                                class="h-11 rounded-xl bg-red-600 px-5 text-sm font-bold text-white shadow-lg shadow-red-500/25 transition hover:bg-red-500 dark:bg-red-500/15 dark:text-red-200 dark:ring-1 dark:ring-red-500/30 dark:hover:bg-red-500/25">
                            Delete Member
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Member Panel --}}
        <div x-show="editOpen"
             x-cloak
             @keydown.escape.window="closeEdit()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="editOpen"
                 x-transition.opacity
                 @click="closeEdit()"
                 class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm dark:bg-black/70"></div>

            <div x-show="editOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                 x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                 x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                 class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-cyan-100 bg-white shadow-2xl shadow-slate-300/70 dark:border-cyan-400/10 dark:bg-[#020617] dark:shadow-cyan-950/30">

                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5 dark:border-cyan-400/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-600 dark:text-cyan-400">
                            Account Settings
                        </p>
                        <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                            Update Member
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Change profile details, role, or access status.
                        </p>
                    </div>

                    <button type="button"
                            @click="closeEdit()"
                            aria-label="Close edit panel"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:bg-cyan-400/10 dark:hover:text-cyan-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="updateAction()" class="space-y-5 p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" x-model="editUser.id">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Full Name</span>
                            <input
                                x-model="editUser.name"
                                name="name"
                                type="text"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-cyan-300/50"
                            >
                            @error('name', 'editUser')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</span>
                            <input
                                x-model="editUser.email"
                                name="email"
                                type="email"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-cyan-300/50"
                            >
                            @error('email', 'editUser')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Role</span>
                            <select
                                x-model="editUser.role"
                                name="role"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-300 dark:focus:border-cyan-300/50">
                                <option value="SOC Analyst">SOC Analyst</option>
                                <option value="Super Admin">Super Admin</option>
                            </select>
                            @error('role', 'editUser')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Status</span>
                            <select
                                x-model="editUser.status"
                                name="status"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-300 dark:focus:border-cyan-300/50">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            @error('status', 'editUser')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-cyan-400/10 sm:flex-row sm:justify-end">
                        <button type="button"
                                @click="closeEdit()"
                                class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                            Cancel
                        </button>

                        <button type="submit"
                                class="h-11 rounded-xl bg-cyan-600 px-5 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 transition hover:bg-cyan-500 dark:bg-cyan-400/15 dark:text-cyan-200 dark:ring-1 dark:ring-cyan-400/30 dark:hover:bg-cyan-400/25">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Invite Member Panel --}}
        <div x-show="inviteOpen"
             x-cloak
             @keydown.escape.window="closeInvite()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="inviteOpen"
                 x-transition.opacity
                 @click="closeInvite()"
                 class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm dark:bg-black/70"></div>

            <div x-show="inviteOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                 x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                 x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                 class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-cyan-100 bg-white shadow-2xl shadow-slate-300/70 dark:border-cyan-400/10 dark:bg-[#020617] dark:shadow-cyan-950/30">

                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5 dark:border-cyan-400/10">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-600 dark:text-cyan-400">
                            New Access
                        </p>
                        <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">
                            Invite Member
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Send an invitation and assign the right SOC permissions.
                        </p>
                    </div>

                    <button type="button"
                            @click="closeInvite()"
                            aria-label="Close invite panel"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:bg-cyan-400/10 dark:hover:text-cyan-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('users.invite') }}" class="space-y-5 p-5">
                    @csrf
    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Full Name</span>
                            <input
                                x-model="inviteName"
                                name="name"
                                type="text"
                                required
                                placeholder="Member name"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-cyan-300/50"
                            >
                            @error('name', 'invite')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Email</span>
                            <input
                                x-model="inviteEmail"
                                name="email"
                                type="email"
                                required
                                placeholder="name@company.com"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-cyan-300/50"
                            >
                            @error('email', 'invite')
                                <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <label class="space-y-2 block">
                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Role</span>
                        <select
                            x-model="inviteRole"
                            name="role"
                            required
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/10 dark:border-cyan-400/10 dark:bg-[#07111f] dark:text-slate-300 dark:focus:border-cyan-300/50">
                            <option value="SOC Analyst">SOC Analyst</option>
                            <option value="Super Admin">Super Admin</option>
                        </select>
                        @error('role', 'invite')
                            <span class="block text-xs font-semibold text-red-500">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="button"
                                @click="inviteRole = 'SOC Analyst'"
                                class="rounded-xl border p-3 text-left transition"
                                :class="inviteRole === 'SOC Analyst'
                                    ? 'border-cyan-300 bg-cyan-50 text-cyan-800 dark:border-cyan-400/40 dark:bg-cyan-400/10 dark:text-cyan-200'
                                    : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-cyan-200 dark:border-cyan-400/10 dark:bg-slate-900/50 dark:text-slate-400'">
                            <span class="block text-xs font-black">SOC Analyst</span>
                            <span class="mt-1 block text-[11px] leading-4 opacity-80">Triage and review</span>
                        </button>

                        <button type="button"
                                @click="inviteRole = 'Super Admin'"
                                class="rounded-xl border p-3 text-left transition"
                                :class="inviteRole === 'Super Admin'
                                    ? 'border-purple-300 bg-purple-50 text-purple-800 dark:border-purple-400/40 dark:bg-purple-400/10 dark:text-purple-200'
                                    : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-purple-200 dark:border-cyan-400/10 dark:bg-slate-900/50 dark:text-slate-400'">
                            <span class="block text-xs font-black">Super Admin</span>
                            <span class="mt-1 block text-[11px] leading-4 opacity-80">Full control</span>
                        </button>
                    </div>

                    <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/10 dark:bg-amber-400/5 dark:text-amber-300">
                        The invite will be sent by email. The member can finish setup after accepting access.
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-cyan-400/10 sm:flex-row sm:justify-end">
                        <button type="button"
                                @click="closeInvite()"
                                class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-cyan-400/10 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                            Cancel
                        </button>

                        <button type="submit"
                                class="h-11 rounded-xl bg-cyan-600 px-5 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 transition hover:bg-cyan-500 dark:bg-cyan-400/15 dark:text-cyan-200 dark:ring-1 dark:ring-cyan-400/30 dark:hover:bg-cyan-400/25">
                            Send Invite
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            const initialUsers = @js($usersPayload);
            const usersBaseUrl = @js(url('/users-roles'));

            Alpine.data('usersRolesPage', () => ({
                search: '',
                role: 'all',
                status: 'all',
                inviteOpen: @js($errors->invite->any()),
                editOpen: @js($errors->editUser->any()),
                deleteOpen: false,
                inviteName: @js(old('name', '')),
                inviteEmail: @js(old('email', '')),
                inviteRole: @js(old('role', 'SOC Analyst')),
                editUser: {
                    id: @js(old('user_id', '')),
                    name: @js(old('name', '')),
                    email: @js(old('email', '')),
                    role: @js(old('role', 'SOC Analyst')),
                    status: @js(old('status', 'Active')),
                },
                deleteUser: {
                    id: '',
                    name: '',
                    email: '',
                    role: '',
                },
                users: Array.isArray(initialUsers) ? initialUsers : [],
                roles: [
                    {
                        name: 'Super Admin',
                        tone: 'purple',
                        description: 'Complete platform control, billing, users, and security policy.'
                    },
                    {
                        name: 'SOC Analyst',
                        tone: 'cyan',
                        description: 'Incident triage, alert review, and operational investigation tools.'
                    },
                ],
                get filteredUsers() {
                    const query = this.search.toLowerCase().trim();

                    return this.users.filter((user) => {
                        const matchesRole = this.role === 'all' || user.role === this.role;
                        const matchesStatus = this.status === 'all' || this.statusLabel(user.status) === this.status;
                        const searchable = [
                            user.name,
                            user.email,
                            user.role,
                            user.access,
                            this.statusLabel(user.status),
                        ].join(' ').toLowerCase();

                        return matchesRole && matchesStatus && (!query || searchable.includes(query));
                    });
                },
                get activeUsers() {
                    return this.users.filter((user) => this.statusLabel(user.status) === 'Active').length;
                },
                countByRole(role) {
                    return this.users.filter((user) => user.role === role).length;
                },
                initials(name) {
                    return String(name || '?')
                        .trim()
                        .split(/\s+/)
                        .map((part) => part[0])
                        .join('')
                        .slice(0, 2)
                        .toUpperCase();
                },
                statusLabel(status) {
                    return String(status || 'Inactive').toLowerCase() === 'active' ? 'Active' : 'Inactive';
                },
                roleBadge(role) {
                    if (role === 'Super Admin') {
                        return 'bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-400/10 dark:text-purple-300 dark:ring-purple-400/20';
                    }

                    return 'bg-cyan-50 text-cyan-700 ring-cyan-200 dark:bg-cyan-400/10 dark:text-cyan-300 dark:ring-cyan-400/20';
                },
                roleCard(tone) {
                    if (tone === 'purple') {
                        return 'border-purple-200 bg-purple-50/70 dark:border-purple-400/10 dark:bg-purple-400/5';
                    }

                    return 'border-cyan-200 bg-cyan-50/70 dark:border-cyan-400/10 dark:bg-cyan-400/5';
                },
                clearFilters() {
                    this.search = '';
                    this.role = 'all';
                    this.status = 'all';
                },
                openEdit(user) {
                    this.editUser = {
                        id: user.id,
                        name: user.name || '',
                        email: user.email || '',
                        role: user.role || 'SOC Analyst',
                        status: this.statusLabel(user.status),
                    };
                    this.editOpen = true;
                },
                closeEdit() {
                    this.editOpen = false;
                },
                openDelete(user) {
                    this.deleteUser = {
                        id: user.id,
                        name: user.name || '',
                        email: user.email || '',
                        role: user.role || '',
                    };
                    this.deleteOpen = true;
                },
                closeDelete() {
                    this.deleteOpen = false;
                },
                closeInvite() {
                    this.inviteOpen = false;
                },
                updateAction() {
                    return `${usersBaseUrl}/${this.editUser.id}`;
                },
                deleteAction(user) {
                    return `${usersBaseUrl}/${user?.id || ''}`;
                },
            }));
        });
    </script>
</x-dashboard-layout>
