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
            const params = new URLSearchParams(window.location.search);
            const [summary, setSummary] = useState(null);
            const [feedItems, setFeedItems] = useState([]);
            const [type, setType] = useState(params.get('type') || 'all');
            const [q, setQ] = useState(params.get('q') || '');
            const [error, setError] = useState('');

            const loadFeed = (nextType, nextQ) => {
                const query = new URLSearchParams();

                if (nextType && nextType !== 'all') {
                    query.set('type', nextType);
                }

                if (nextQ.trim()) {
                    query.set('q', nextQ.trim());
                }

                const queryString = query.toString();
                const url = queryString ? `/labs/react/feed?${queryString}` : '/labs/react/feed';

                window.history.replaceState({}, '', queryString ? `/labs/react?${queryString}` : '/labs/react');

                return fetch(url)
                    .then((res) => {
                        if (!res.ok) throw new Error('Feed failed');
                        return res.json();
                    })
                    .then((feedData) => {
                        setFeedItems(feedData.items || []);
                    });
            };

            useEffect(() => {
                fetch('/labs/react/summary')
                    .then((res) => {
                        if (!res.ok) throw new Error('Summary failed');
                        return res.json();
                    })
                    .then(setSummary)
                    .catch(() => setError('Could not load summary data right now.'));

                loadFeed(type, q).catch(() => setError('Could not load feed data right now.'));
            }, []);

            const onApplyFilters = (event) => {
                event.preventDefault();
                setError('');
                loadFeed(type, q).catch(() => setError('Could not load feed data right now.'));
            };

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
                        React.createElement('p', { className: 'mt-4 max-w-2xl text-slate-600' }, 'Now connected to live Laravel summary and filterable feed endpoints so migration can move one screen at a time.'),
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
                            'form',
                            { className: 'mt-4 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-4', onSubmit: onApplyFilters },
                            React.createElement('select', {
                                className: 'rounded-md border border-slate-300 px-3 py-2 text-sm',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            },
                                React.createElement('option', { value: 'all' }, 'All types'),
                                React.createElement('option', { value: 'post' }, 'Posts'),
                                React.createElement('option', { value: 'poll' }, 'Polls'),
                                React.createElement('option', { value: 'event' }, 'Events'),
                                React.createElement('option', { value: 'suggestion' }, 'Suggestions'),
                                React.createElement('option', { value: 'listing' }, 'Listings')
                            ),
                            React.createElement('input', {
                                className: 'rounded-md border border-slate-300 px-3 py-2 text-sm md:col-span-2',
                                type: 'text',
                                value: q,
                                placeholder: 'Search title/body...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', {
                                className: 'rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500',
                                type: 'submit',
                            }, 'Apply filters')
                        ),
                        React.createElement(
                            'div',
                            { className: 'mt-4 grid gap-4 md:grid-cols-2' },
                            ...(feedItems.length
                                ? feedItems.map((item, index) => React.createElement(FeedItem, { key: `${item.type}-${index}`, item }))
                                : [React.createElement('p', { key: 'loading', className: 'text-sm text-slate-500' }, 'No feed items match these filters yet.')])
                        )
                    )
                )
            );
        };

        createRoot(document.getElementById('react-app')).render(React.createElement(App));
    </script>
</body>
</html>
