<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">User Management</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Manage organization members, roles, profiles, and access levels
                </p>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 glass-panel space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 uppercase font-semibold border-b border-slate-800 pb-2">
                            <th class="py-2.5 px-3">User</th>
                            <th class="py-2.5 px-3">Email</th>
                            <th class="py-2.5 px-3">Role</th>
                            <th class="py-2.5 px-3">Profile</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        <tr class="hover:bg-slate-800/20 transition text-slate-200">
                            <td class="py-3 px-3 font-semibold text-white flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                                    O
                                </div>
                                <span>Oleg Admin</span>
                            </td>
                            <td class="py-3 px-3 text-slate-400">admin@acme.com</td>
                            <td class="py-3 px-3 text-indigo-400 font-medium">CEO</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-semibold">
                                    Administrator
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center gap-1 text-emerald-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
</script>
