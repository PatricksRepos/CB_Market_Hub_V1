<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @php
            $toastMessage = session('status');
            $toastType = session('error') || $errors->any() ? 'error' : ($toastMessage ? 'success' : null);
            $toastBody = session('error') ?: ($errors->any() ? $errors->first() : $toastMessage);
        @endphp

        <div id="appToast"
             class="fixed bottom-4 right-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm shadow-lg transition {{ $toastBody ? '' : 'hidden' }} {{ $toastType === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white' }}">
            {{ $toastBody }}
        </div>

        @auth
            <script>
                (function () {
                    const toast = document.getElementById('appToast');
                    const badge = document.getElementById('navUnreadBadge');
                    let unreadCount = Number(badge?.textContent || 0);

                    function showToast(message, type = 'success') {
                        if (!toast) return;
                        toast.textContent = message;
                        toast.classList.remove('hidden', 'bg-green-600', 'bg-red-600');
                        toast.classList.add(type === 'error' ? 'bg-red-600' : 'bg-green-600');

                        window.clearTimeout(window.__appToastTimer);
                        window.__appToastTimer = window.setTimeout(() => {
                            toast.classList.add('hidden');
                        }, 3500);
                    }

                    if (toast && !toast.classList.contains('hidden')) {
                        window.setTimeout(() => toast.classList.add('hidden'), 3500);
                    }

                    async function pollNotifications() {
                        try {
                            const response = await fetch("{{ route('notifications.unread-count') }}", {
                                headers: { Accept: 'application/json' },
                            });

                            if (!response.ok) return;

                            const data = await response.json();
                            const nextCount = Number(data.unread_count || 0);

                            if (badge) {
                                badge.textContent = String(nextCount);
                                badge.classList.toggle('hidden', nextCount < 1);
                            }

                            if (nextCount > unreadCount) {
                                showToast('You have new notifications.', 'success');

                                if ('Notification' in window && Notification.permission === 'granted') {
                                    new Notification('CB Community', {
                                        body: 'You have new notifications.',
                                    });
                                }
                            }

                            unreadCount = nextCount;
                        } catch (error) {
                            // ignore transient polling failures
                        }
                    }

                    if ('Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission().catch(() => {});
                    }

                    window.showAppToast = showToast;
                    window.setInterval(pollNotifications, 15000);
                })();
            </script>
        @else
            <script>
                (function () {
                    const toast = document.getElementById('appToast');
                    if (toast && !toast.classList.contains('hidden')) {
                        window.setTimeout(() => toast.classList.add('hidden'), 3500);
                    }
                })();
            </script>
        @endauth
    </body>
</html>
