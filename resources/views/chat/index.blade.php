<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Community Chat (Public)</h2>
            <div class="text-sm text-gray-500">Public room • visible to everyone (auto refresh)</div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="rounded border bg-yellow-50 p-3 text-yellow-900">{{ session('status') }}</div>
            @endif

            <div class="rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                This is an open platform chat. Do not share private contact or payment details here. Use marketplace <strong>Contacts</strong> for buyer/seller private messaging.
            </div>

            <div class="bg-white rounded-lg border p-4">
                <div id="chatBox" class="h-[60vh] overflow-y-auto space-y-2 pr-2">
                    @php $lastId = 0; @endphp
                    @foreach($messages as $m)
                        @php $lastId = max($lastId, $m->id); @endphp
                        <div class="border rounded-lg p-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-start gap-3">
                                    @if($m->user?->avatar_url)
                                        <img src="{{ $m->user->avatar_url }}" alt="{{ $m->user?->name ?? 'User' }} avatar" class="h-16 w-16 rounded-full object-cover border">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 text-xl font-semibold">{{ strtoupper(substr($m->user?->name ?? 'U',0,1)) }}</div>
                                    @endif

                                    <div class="text-sm font-semibold text-gray-800">
                                        @if($m->is_deleted)
                                            Message removed
                                        @else
                                            {{ $m->user?->name ?? 'User' }}
                                        @endif
                                        <span class="text-xs font-normal text-gray-500">• {{ $m->created_at->diffForHumans() }}</span>

                                        @if(!$m->is_deleted)
                                            <div class="mt-1 text-gray-800 whitespace-pre-wrap font-normal">{{ $m->body }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    @auth
                                        <form method="POST" action="{{ route('chat.report',$m) }}">
                                            @csrf
                                            <input type="hidden" name="reason" value="reported from community chat">
                                            <button class="text-xs text-gray-500 hover:underline" type="submit">Report</button>
                                        </form>

                                        @if(auth()->id() === $m->user_id || auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('chat.delete',$m) }}" onsubmit="return confirm('Remove this message?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs text-red-600 hover:underline" type="submit">Remove</button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 border-t pt-4">
                    @auth
                        <form method="POST" action="{{ route('chat.send') }}" class="flex gap-2">
                            @csrf
                            <input name="body" maxlength="500" required
                                   class="flex-1 rounded-lg border-gray-300"
                                   value="{{ old('body', $prefillMessage ?? '') }}"
                                   placeholder="Say something in the public room… (keep it chill)">
                            <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">
                                Send
                            </button>
                        </form>
                        <div class="text-xs text-gray-500 mt-2">
                            Tip: If someone is being weird, hit Report.
                        </div>
                    @else
                        <div class="text-gray-600">
                            <a class="underline" href="{{ route('login') }}">Log in</a> to join public chat.
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            let lastId = {{ (int)$lastId }};
            const box = document.getElementById('chatBox');

            function scrollToBottomIfNearBottom() {
                const nearBottom = (box.scrollHeight - box.scrollTop - box.clientHeight) < 200;
                if (nearBottom) box.scrollTop = box.scrollHeight;
            }

            async function poll() {
                try {
                    const res = await fetch("{{ route('chat.fetch') }}?after_id=" + lastId, { headers: { "Accept": "application/json" }});
                    if (!res.ok) return;
                    const data = await res.json();
                    if (!data.messages || data.messages.length === 0) return;

                    for (const m of data.messages) {
                        lastId = Math.max(lastId, m.id);

                        const wrap = document.createElement('div');
                        wrap.className = "border rounded-lg p-2";

                        const header = document.createElement('div');
                        header.className = "flex items-start justify-between gap-2";

                        const leftWrap = document.createElement('div');
                        leftWrap.className = "flex items-start gap-3";

                        let avatar;
                        if (m.avatar_url) {
                            avatar = document.createElement('img');
                            avatar.src = m.avatar_url;
                            avatar.alt = (m.name || 'User') + ' avatar';
                            avatar.className = "h-16 w-16 rounded-full object-cover border";
                        } else {
                            avatar = document.createElement('div');
                            avatar.className = "h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 text-xl font-semibold";
                            avatar.textContent = (m.name || 'U').slice(0, 1).toUpperCase();
                        }

                        const left = document.createElement('div');
                        left.className = "text-sm font-semibold text-gray-800";
                        left.textContent = (m.is_deleted ? "Message removed" : m.name) + (m.created_at ? " • " + m.created_at : "");

                        if (!m.is_deleted) {
                            const body = document.createElement('div');
                            body.className = "mt-1 text-gray-800 whitespace-pre-wrap font-normal";
                            body.textContent = m.body;
                            left.appendChild(body);
                        }

                        leftWrap.appendChild(avatar);
                        leftWrap.appendChild(left);

                        const right = document.createElement('div');
                        right.className = "text-xs text-gray-400";
                        right.textContent = "";

                        header.appendChild(leftWrap);
                        header.appendChild(right);

                        wrap.appendChild(header);
                        box.appendChild(wrap);
                    }

                    scrollToBottomIfNearBottom();
                } catch (e) {
                    // ignore
                }
            }

            box.scrollTop = box.scrollHeight;
            setInterval(poll, 2500);
        })();
    </script>
</x-app-layout>
