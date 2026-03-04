<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Moderation Queue</h2>
            <form method="GET" action="{{ route('moderation.index') }}" class="flex items-center gap-2">
                <select name="status" class="rounded border-gray-300 text-sm">
                    @foreach(['all' => 'All', 'open' => 'Open', 'reviewing' => 'Reviewing', 'resolved' => 'Resolved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded border px-3 py-1.5 text-sm hover:bg-gray-50">Filter</button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border">
                <div class="border-b p-4 font-semibold">Content Reports</div>
                <div class="divide-y">
                    @forelse($reports as $report)
                        <div class="p-4 space-y-2">
                            <div class="text-sm text-gray-700">
                                <span class="font-medium">#{{ $report->id }}</span>
                                • {{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}
                                • reason: <span class="font-medium">{{ $report->reason }}</span>
                                • status: <span class="font-medium">{{ $report->status }}</span>
                            </div>
                            <div class="text-xs text-gray-500">By {{ $report->reporter?->name ?? 'User' }} • {{ $report->created_at->diffForHumans() }}</div>
                            @if($report->details)
                                <div class="text-sm text-gray-800">{{ $report->details }}</div>
                            @endif

                            <form method="POST" action="{{ route('moderation.reports.update', $report) }}" class="grid sm:grid-cols-12 gap-2 items-end">
                                @csrf
                                @method('PATCH')
                                <div class="sm:col-span-3">
                                    <label class="block text-xs text-gray-500">Set status</label>
                                    <select name="status" class="mt-1 w-full rounded border-gray-300 text-sm">
                                        @foreach(['open', 'reviewing', 'resolved', 'rejected'] as $statusOption)
                                            <option value="{{ $statusOption }}" @selected($report->status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-7">
                                    <label class="block text-xs text-gray-500">Resolution notes</label>
                                    <input type="text" name="resolution_notes" value="{{ $report->resolution_notes }}" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="Optional moderator notes">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="w-full rounded border px-3 py-2 text-sm hover:bg-gray-50">Save</button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="p-6 text-gray-600">No reports found for this filter.</div>
                    @endforelse
                </div>
                <div class="p-4">{{ $reports->links() }}</div>
            </div>

            <div class="bg-white rounded-lg border">
                <div class="border-b p-4 font-semibold">Recent Chat Reports</div>
                <div class="divide-y">
                    @forelse($chatReports as $chatReport)
                        <div class="p-4">
                            <div class="text-sm text-gray-700">Message #{{ $chatReport->chat_message_id }} reported by {{ $chatReport->user?->name ?? 'User' }}</div>
                            <div class="text-xs text-gray-500">Reason: {{ $chatReport->reason ?: 'No reason provided' }} • {{ $chatReport->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="p-6 text-gray-600">No chat reports.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
