<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React App</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-950 text-slate-100">
    <div id="react-site-app"></div>

    <script type="module">
        import React, { useEffect, useState } from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const navItems = [
            { key: 'feed', label: 'Feed', href: '/labs/feed-react' },
            { key: 'posts', label: 'Posts', href: '/posts' },
            { key: 'polls', label: 'Polls', href: '/polls' },
            { key: 'marketplace', label: 'Marketplace', href: '/marketplace' },
            { key: 'events', label: 'Events', href: '/events' },
            { key: 'suggestions', label: 'Suggestions', href: '/suggestions' },
            { key: 'chat', label: 'Chat', href: '/chat' },
            { key: 'react', label: 'React App', href: '/labs/app-react', active: true },
        ];

        const sectionOrder = ['posts', 'polls', 'marketplace', 'events', 'suggestions', 'chat'];

        const SectionCard = ({ item }) => React.createElement('a', {
            href: item.href || '#',
            className: 'block rounded-xl border border-slate-800 bg-slate-900/80 p-4 hover:border-indigo-400/50',
        },
            React.createElement('p', { className: 'text-sm font-semibold text-white' }, item.title || 'Untitled'),
            React.createElement('p', { className: 'mt-1 text-sm text-slate-300' }, item.excerpt || ''),
            React.createElement('p', { className: 'mt-2 text-xs text-slate-400' }, `${item.author || 'Unknown'} • ${item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown'}`)
        );

        const App = () => {
            const [data, setData] = useState({ counts: {}, sections: {} });
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState('');

            useEffect(() => {
                fetch('/labs/react/site-overview')
                    .then((res) => {
                        if (!res.ok) throw new Error('Failed to load React site data');
                        return res.json();
                    })
                    .then((payload) => setData(payload || { counts: {}, sections: {} }))
                    .catch(() => setError('Unable to load overview data right now.'))
                    .finally(() => setLoading(false));
            }, []);

            return React.createElement('main', { className: 'min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950' },
                React.createElement('header', { className: 'sticky top-0 z-20 border-b border-slate-800/90 bg-slate-950/95 backdrop-blur' },
                    React.createElement('div', { className: 'mx-auto max-w-7xl px-6 py-4' },
                        React.createElement('a', { href: '/', className: 'text-lg font-bold text-white' }, 'CB Community'),
                        React.createElement('nav', { className: 'mt-3 flex flex-wrap gap-2' },
                            ...navItems.map((item) => React.createElement('a', {
                                key: item.key,
                                href: item.href,
                                className: item.active
                                    ? 'rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white'
                                    : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-slate-500 hover:text-white'
                            }, item.label))
                        )
                    )
                ),
                React.createElement('div', { className: 'mx-auto max-w-7xl px-6 py-10' },
                    React.createElement('div', { className: 'rounded-3xl border border-slate-800 bg-slate-900/70 p-6 shadow-2xl' },
                        React.createElement('h1', { className: 'text-3xl font-bold text-white' }, 'React implementation across the site'),
                        React.createElement('p', { className: 'mt-2 text-sm text-slate-300' }, 'This React app now covers every major area with section previews, while Blade routes remain untouched for safe rollout.'),
                        React.createElement('div', { className: 'mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4' },
                            ...Object.entries(data.counts || {}).map(([key, value]) => React.createElement('div', { key, className: 'rounded-xl border border-slate-700 bg-slate-800/70 px-4 py-3' },
                                React.createElement('p', { className: 'text-xs uppercase tracking-wide text-slate-400' }, key.replace('_', ' ')),
                                React.createElement('p', { className: 'mt-1 text-2xl font-semibold text-white' }, String(value || 0))
                            ))
                        )
                    ),
                    loading && React.createElement('p', { className: 'mt-6 text-sm text-slate-300' }, 'Loading sections...'),
                    error && React.createElement('p', { className: 'mt-6 rounded-lg border border-red-700 bg-red-950/30 p-3 text-sm text-red-200' }, error),
                    !loading && React.createElement('div', { className: 'mt-8 grid gap-6 lg:grid-cols-2' },
                        ...sectionOrder.map((section) => React.createElement('section', { key: section, className: 'rounded-2xl border border-slate-800 bg-slate-900/60 p-5' },
                            React.createElement('div', { className: 'mb-4 flex items-center justify-between' },
                                React.createElement('h2', { className: 'text-lg font-semibold capitalize text-white' }, section),
                                React.createElement('a', { href: navItems.find((item) => item.key === section)?.href || '#', className: 'text-sm text-indigo-300 hover:text-indigo-200' }, 'Open')
                            ),
                            React.createElement('div', { className: 'space-y-3' },
                                ...((data.sections?.[section] || []).length
                                    ? data.sections[section].map((item, i) => React.createElement(SectionCard, { key: `${section}-${i}`, item }))
                                    : [React.createElement('p', { key: `${section}-empty`, className: 'text-sm text-slate-400' }, 'No items yet.')])
                            )
                        ))
                    )
                )
            );
        };

        createRoot(document.getElementById('react-site-app')).render(React.createElement(App));
    </script>
</body>
</html>
