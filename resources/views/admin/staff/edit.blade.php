@extends('layouts.admin')

@section('title', 'Edit Staff Member - laamtex')
@section('page_title', 'Edit Staff Member')

@section('content')
<div class="max-w-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Staff Details</h3>
        <p class="text-sm text-slate-300 mt-1">
            Currently <span class="font-semibold text-slate-200">{{ $user->role_label }}</span> ·
            last updated {{ $user->updated_at->format('d M Y') }}
        </p>
    </div>

    <form action="{{ route('admin.staff.update', $user->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Full Name <span class="text-pink-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. Rahim Uddin">
            @error('name')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="email" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Email <span class="text-pink-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                       placeholder="e.g. rahim@outfitt.com">
                @error('email')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                       placeholder="01XXXXXXXXX">
                @error('phone')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label for="role" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Role <span class="text-pink-500">*</span></label>
            <select id="role" name="role" required onchange="document.getElementById('roleHint').textContent = this.value === 'moderator' ? 'Orders only, no delete.' : 'Full access to everything.'"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200">
                <option value="moderator" {{ old('role', $user->role) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
            <p id="roleHint" class="text-xs text-slate-400 mt-1">{{ old('role', $user->role) === 'super_admin' ? 'Full access to everything.' : 'Orders only, no delete.' }}</p>
            @error('role')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">New Password</label>
                <input type="password" id="password" name="password"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                       placeholder="Leave blank to keep current">
                @error('password')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                       placeholder="Repeat new password">
            </div>
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Add Staff Member
            </button>
            <a href="{{ route('admin.staff.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
