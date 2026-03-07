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

        <script>
            (function () {
                try {
                    const savedTheme = localStorage.getItem('themePreference');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-theme', savedTheme || systemTheme);
                } catch (error) {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 50%, #f0fdfa 100%);">
            <div class="w-full max-w-md flex justify-end px-4 sm:px-0">
                <button type="button" onclick="toggleThemeMode()" class="text-sm px-3 py-2 rounded-lg border text-indigo-700 hover:bg-indigo-50">
                    <span data-theme-toggle-label>Dark mode</span>
                </button>
            </div>
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="brand-card w-full sm:max-w-md mt-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
