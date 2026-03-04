<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React Frontend</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-50 text-slate-900">
    <div id="react-app"></div>

    <script type="module">
        import React from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const StatCard = ({ label, value }) => React.createElement(
            'div',
            { className: 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm' },
            React.createElement('p', { className: 'text-sm text-slate-500' }, label),
            React.createElement('p', { className: 'mt-2 text-2xl font-semibold text-slate-900' }, value)
        );

        const App = () => React.createElement(
            'main',
            { className: 'min-h-screen' },
            React.createElement(
                'div',
                { className: 'mx-auto max-w-5xl px-6 py-16' },
                React.createElement(
                    'div',
                    { className: 'rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200' },
                    React.createElement('p', { className: 'text-sm font-semibold uppercase tracking-wider text-indigo-600' }, 'CB Market Hub'),
                    React.createElement('h1', { className: 'mt-3 text-4xl font-bold' }, 'React frontend lab is live'),
                    React.createElement('p', { className: 'mt-4 max-w-2xl text-slate-600' }, 'This isolated /labs/react page is rendered by React so you can migrate Blade screens incrementally without changing existing routes.'),
                    React.createElement(
                        'div',
                        { className: 'mt-8 grid gap-4 sm:grid-cols-3' },
                        React.createElement(StatCard, { label: 'UI runtime', value: 'React 18' }),
                        React.createElement(StatCard, { label: 'Styling', value: 'Tailwind CSS' }),
                        React.createElement(StatCard, { label: 'Next move', value: 'Migrate Feed' })
                    )
                )
            )
        );

        createRoot(document.getElementById('react-app')).render(React.createElement(App));
    </script>
</body>
</html>
