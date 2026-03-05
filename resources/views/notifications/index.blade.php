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
            @forelse($notifications as $notification)
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'Notification' }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>

                        @if(is_null($notification->read_at))
                            <form method="POST" action="{{ route('notifications.read-one', $notification->id) }}">
                                @csrf
                                <button type="submit" class="rounded border px-3 py-1 text-xs hover:bg-gray-50">Mark read</button>
                            </form>
                        @else
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600">Read</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">You have no notifications.</div>
            @endforelse

            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
