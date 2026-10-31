@extends('layouts.admin')

@section('title', 'Contact Message - laamtex')
@section('page_title', 'Contact Message')

@section('content')
<div class="space-y-6">
    <div class="bg-slate-900/50 border border-slate-800/50 rounded-2xl p-6 space-y-4 text-slate-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Name</p>
                <p class="font-semibold">{{ $contactMessage->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Email</p>
                <p class="font-semibold">{{ $contactMessage->email ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Phone</p>
                <p class="font-semibold">{{ $contactMessage->phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Subject</p>
                <p class="font-semibold">{{ $contactMessage->subject ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Date</p>
                <p class="font-semibold">{{ $contactMessage->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Status</p>
                <p class="font-semibold">{{ $contactMessage->is_read ? 'Read' : 'Unread' }}</p>
            </div>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-400">Message</p>
            <p class="mt-2 whitespace-pre-wrap leading-relaxed">{{ $contactMessage->message }}</p>
        </div>
    </div>

    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.contact-messages.index') }}" class="px-4 py-2 bg-slate-800/50 text-slate-300 rounded-lg text-sm hover:bg-slate-700/50">Back</a>
        @if(! $contactMessage->is_read)
            <form action="{{ route('admin.contact-messages.read', $contactMessage) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-500">Mark Read</button>
            </form>
        @endif
        <form action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" method="POST" onsubmit="return confirm('Delete this message?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-500">Delete</button>
        </form>
    </div>
</div>
@endsection
