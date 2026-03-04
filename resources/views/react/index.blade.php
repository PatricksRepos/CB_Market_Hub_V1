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
        import React, { useEffect, useState } from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const StatCard = ({ label, value }) => React.createElement(
            'div',
            { className: 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm' },
            React.createElement('p', { className: 'text-sm text-slate-500' }, label),
            React.createElement('p', { className: 'mt-2 text-2xl font-semibold text-slate-900' }, value)
        );

        const FeedItem = ({ item }) => React.createElement(
            'article',
            { className: 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm' },
            React.createElement('p', { className: 'text-xs uppercase tracking-wide text-indigo-600' }, item.type),
            React.createElement('h3', { className: 'mt-1 text-base font-semibold text-slate-900' }, item.title || '(untitled)'),
            React.createElement('p', { className: 'mt-2 text-sm text-slate-600' }, item.excerpt || ''),
            React.createElement('p', { className: 'mt-3 text-xs text-slate-500' }, `By ${item.author || 'Unknown'} • ${new Date(item.created_at).toLocaleString()}`)
        );

        const App = () => {
            const [summary, setSummary] = useState(null);
            const [feedItems, setFeedItems] = useState([]);
            const [error, setError] = useState('');

            useEffect(() => {
                Promise.all([
                    fetch('/labs/react/summary').then((res) => {
                        if (!res.ok) throw new Error('Summary failed');
                        return res.json();
                    }),
                    fetch('/labs/react/feed').then((res) => {
                        if (!res.ok) throw new Error('Feed failed');
                        return res.json();
                    }),
                ])
                    .then(([summaryData, feedData]) => {
                        setSummary(summaryData);
                        setFeedItems(feedData.items || []);
                    })
                    .catch(() => setError('Could not load React lab data right now.'));
            }, []);

            return React.createElement(
                'main',
                { className: 'min-h-screen' },
                React.createElement(
                    'div',
                    { className: 'mx-auto max-w-6xl px-6 py-16' },
                    React.createElement(
                        'div',
                        { className: 'rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200' },
                        React.createElement('p', { className: 'text-sm font-semibold uppercase tracking-wider text-indigo-600' }, 'CB Market Hub'),
                        React.createElement('h1', { className: 'mt-3 text-4xl font-bold' }, 'React frontend lab is live'),
                        React.createElement('p', { className: 'mt-4 max-w-2xl text-slate-600' }, 'Now connected to live Laravel summary and feed preview endpoints so migration can move one screen at a time.'),
                        error && React.createElement('p', { className: 'mt-4 text-sm text-red-600' }, error),
                        React.createElement(
                            'div',
                            { className: 'mt-8 grid gap-4 sm:grid-cols-3' },
                            React.createElement(StatCard, { label: 'UI runtime', value: 'React 18' }),
                            React.createElement(StatCard, { label: 'Styling', value: 'Tailwind CSS' }),
                            React.createElement(StatCard, { label: 'Next move', value: 'Port Feed Cards' })
                        ),
                        React.createElement('h2', { className: 'mt-10 text-xl font-semibold' }, 'Live backend summary'),
                        React.createElement(
                            'div',
                            { className: 'mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5' },
                            React.createElement(StatCard, { label: 'Posts', value: summary ? String(summary.posts) : '...' }),
                            React.createElement(StatCard, { label: 'Polls', value: summary ? String(summary.polls) : '...' }),
                            React.createElement(StatCard, { label: 'Events', value: summary ? String(summary.events) : '...' }),
                            React.createElement(StatCard, { label: 'Suggestions', value: summary ? String(summary.suggestions) : '...' }),
                            React.createElement(StatCard, { label: 'Listings', value: summary ? String(summary.listings) : '...' })
                        ),
                        React.createElement('h2', { className: 'mt-10 text-xl font-semibold' }, 'Live feed preview'),
                        React.createElement(
                            'div',
                            { className: 'mt-4 grid gap-4 md:grid-cols-2' },
                            ...(feedItems.length
                                ? feedItems.map((item, index) => React.createElement(FeedItem, { key: `${item.type}-${index}`, item }))
                                : [React.createElement('p', { key: 'loading', className: 'text-sm text-slate-500' }, 'Loading feed preview...')])
                        )
                    )
                )
            );
        };

        createRoot(document.getElementById('react-app')).render(React.createElement(App));
    </script>
</body>
</html>
