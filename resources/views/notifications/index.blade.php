<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            <form method="POST" action="{{ route('notifications.read') }}">
                @csrf
                <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" type="submit">Mark all read</button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            @forelse($notifications as $notification)
                <div class="bg-white rounded-lg border p-4">
                    <div class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'Notification' }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">You have no notifications.</div>
            @endforelse

            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
