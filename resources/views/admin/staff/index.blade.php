@extends('layouts.admin')

@section('title', 'Staff & Roles - laamtex')
@section('page_title', 'Staff & Roles')

@section('content')
<div class="space-y-6">

    <div class="rounded-2xl border border-slate-800/50 bg-slate-900/60 p-4 text-sm text-slate-300">
        <p class="font-bold text-white mb-1">How access works</p>
        <ul class="list-disc pl-5 space-y-1 text-slate-400">
            <li><span class="font-semibold text-pink-300">Super Admin</span> — full access to everything: products, categories, customers, shipping, sliders, coupons, settings and staff. Can delete orders.</li>
            <li><span class="font-semibold text-sky-300">Moderator</span> — orders only: can view orders and change their status, but cannot delete orders or open any other section.</li>
        </ul>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <p class="text-sm text-slate-300">Add or remove admin panel members and change their role.</p>
        <a href="{{ route('admin.staff.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            + Add Staff Member
        </a>
    </div>

    <form action="{{ route('admin.staff.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative w-64">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..."
                   class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 pl-9 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder:text-slate-300">
            <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <select name="role" onchange="this.form.submit()"
                class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">All Roles</option>
            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="moderator" {{ request('role') === 'moderator' ? 'selected' : '' }}>Moderator</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold transition">Filter</button>
        @if(request()->filled('search') || request()->filled('role'))
            <a href="{{ route('admin.staff.index') }}" class="text-sm font-semibold text-pink-400 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 py-4">Member</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Orders Touched</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($staff as $member)
                        @php $isMe = $member->id === auth()->id(); @endphp
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center font-bold text-white flex-shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-200">
                                            {{ $member->name }}
                                            @if($isMe)
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-300 bg-purple-500/15 rounded px-1.5 py-0.5 ml-1">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">{{ $member->orders_count }} orders placed as customer</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="text-slate-200">{{ $member->email }}</div>
                                @if($member->phone)
                                    <div class="text-slate-400 font-mono text-xs">{{ $member->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $member->isSuperAdmin() ? 'bg-pink-500/10 text-pink-400 border-pink-500/20' : 'bg-sky-500/10 text-sky-400 border-sky-500/20' }}">
                                    {{ $member->role_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $member->orders_count }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $member->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.staff.edit', $member->id) }}" class="p-2 text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition font-semibold">
                                        Edit
                                    </a>
                                    @unless($isMe)
                                        <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Remove {{ $member->name }} from the admin team?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-pink-400 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition font-semibold">
                                                Remove
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center text-slate-300">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/50">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
