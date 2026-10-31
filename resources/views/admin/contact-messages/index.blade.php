@extends('layouts.admin')

@section('title', 'Contact Messages - laamtex')
@section('page_title', 'Contact Messages')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg text-base font-semibold">{{ session('success') }}</div>
    @endif

    <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl overflow-hidden">
        <table class="w-full text-sm text-slate-300">
            <thead class="bg-slate-800/60 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email/Phone</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Message</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse($messages as $message)
                    <tr class="{{ $message->is_read ? '' : 'bg-slate-800/30' }}">
                        <td class="px-4 py-3 font-semibold text-white">{{ $message->name }}</td>
                        <td class="px-4 py-3">
                            @if($message->email)<div>{{ $message->email }}</div>@endif
                            @if($message->phone)<div>{{ $message->phone }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ $message->subject ?: '—' }}</td>
                        <td class="px-4 py-3 max-w-xs truncate">{{ $message->message }}</td>
                        <td class="px-4 py-3">
                            @if($message->is_read)
                                <span class="px-2 py-1 rounded text-xs bg-slate-700 text-slate-300">Read</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-rose-500/20 text-rose-300">Unread</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $message->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="inline-block px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs">View</a>
                            @if(! $message->is_read)
                                <form action="{{ route('admin.contact-messages.read', $message) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-xs">Mark Read</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="POST" class="inline" onsubmit="return confirm('Delete this message?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">No contact messages yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $messages->links() }}
    </div>
</div>
@endsection
