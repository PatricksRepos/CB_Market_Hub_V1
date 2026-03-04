<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React Feed</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-100 text-slate-900">
    <div id="react-feed-app"></div>

    <script type="module">
        import React, { useEffect, useState } from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const typeOptions = [
            { value: 'all', label: 'All activity' },
            { value: 'post', label: 'Posts' },
            { value: 'poll', label: 'Polls' },
            { value: 'event', label: 'Events' },
            { value: 'suggestion', label: 'Suggestions' },
            { value: 'listing', label: 'Listings' },
            { value: 'post_comment', label: 'Post comments' },
            { value: 'poll_comment', label: 'Poll comments' },
        ];

        const FeedCard = ({ item }) => React.createElement(
            'article',
            { className: 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm' },
            React.createElement('div', { className: 'flex items-center justify-between gap-2' },
                React.createElement('span', { className: 'rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium uppercase text-indigo-700' }, item.type.replace('_', ' ')),
                React.createElement('span', { className: 'text-xs text-slate-500' }, item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time'),
            ),
            React.createElement('h3', { className: 'mt-3 text-lg font-semibold text-slate-900' }, item.title || '(untitled)'),
            React.createElement('p', { className: 'mt-2 text-sm text-slate-700' }, item.excerpt || ''),
            React.createElement('p', { className: 'mt-3 text-xs text-slate-500' }, `Author: ${item.author || 'Unknown'}`),
        );

        const App = () => {
            const params = new URLSearchParams(window.location.search);
            const [type, setType] = useState(params.get('type') || 'all');
            const [q, setQ] = useState(params.get('q') || '');
            const [items, setItems] = useState([]);
            const [page, setPage] = useState(1);
            const [hasMore, setHasMore] = useState(false);
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState('');

            const fetchFeed = (nextType, nextQ, nextPage, append = false) => {
                setLoading(true);
                const query = new URLSearchParams();

                if (nextType !== 'all') query.set('type', nextType);
                if (nextQ.trim()) query.set('q', nextQ.trim());
                query.set('page', String(nextPage));
                query.set('per_page', '10');

                const queryString = query.toString();
                const apiUrl = `/labs/react/feed?${queryString}`;

                const pageUrlParams = new URLSearchParams();
                if (nextType !== 'all') pageUrlParams.set('type', nextType);
                if (nextQ.trim()) pageUrlParams.set('q', nextQ.trim());
                window.history.replaceState({}, '', pageUrlParams.toString() ? `/labs/feed-react?${pageUrlParams.toString()}` : '/labs/feed-react');

                return fetch(apiUrl)
                    .then((res) => {
                        if (!res.ok) throw new Error('Could not load feed');
                        return res.json();
                    })
                    .then((data) => {
                        const nextItems = data.items || [];
                        setItems((prev) => append ? [...prev, ...nextItems] : nextItems);
                        setHasMore(Boolean(data.pagination && data.pagination.has_more));
                        setPage(nextPage);
                    })
                    .catch(() => setError('Failed to load feed items.'))
                    .finally(() => setLoading(false));
            };

            useEffect(() => {
                fetchFeed(type, q, 1, false);
            }, []);

            return React.createElement(
                'main',
                { className: 'min-h-screen' },
                React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-10' },
                    React.createElement('div', { className: 'mb-6 flex items-center justify-between gap-4' },
                        React.createElement('div', null,
                            React.createElement('p', { className: 'text-sm font-semibold uppercase tracking-wider text-indigo-700' }, 'React Feed (Implementation Track)'),
                            React.createElement('h1', { className: 'mt-1 text-3xl font-bold' }, 'Community Feed in React'),
                            React.createElement('p', { className: 'mt-2 text-sm text-slate-600' }, 'Now with pagination and load-more behavior using Laravel JSON metadata.')
                        ),
                        React.createElement('a', { href: '/', className: 'rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50' }, 'Open Blade feed')
                    ),

                    React.createElement('form', {
                        className: 'rounded-xl border border-slate-200 bg-white p-4 shadow-sm',
                        onSubmit: (e) => {
                            e.preventDefault();
                            setError('');
                            fetchFeed(type, q, 1, false);
                        },
                    },
                        React.createElement('div', { className: 'grid gap-3 md:grid-cols-4' },
                            React.createElement('select', {
                                className: 'rounded-md border border-slate-300 px-3 py-2 text-sm',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            }, ...typeOptions.map((opt) => React.createElement('option', { key: opt.value, value: opt.value }, opt.label))),
                            React.createElement('input', {
                                className: 'rounded-md border border-slate-300 px-3 py-2 text-sm md:col-span-2',
                                value: q,
                                placeholder: 'Search feed...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', {
                                type: 'submit',
                                className: 'rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500',
                            }, 'Apply')
                        )
                    ),

                    error && React.createElement('p', { className: 'mt-4 text-sm text-red-600' }, error),

                    React.createElement('section', { className: 'mt-6 grid gap-4 md:grid-cols-2' },
                        ...(loading && items.length === 0
                            ? [React.createElement('p', { key: 'loading', className: 'text-sm text-slate-600' }, 'Loading feed...')]
                            : items.length
                                ? items.map((item, i) => React.createElement(FeedCard, { key: `${item.type}-${i}`, item }))
                                : [React.createElement('p', { key: 'empty', className: 'text-sm text-slate-600' }, 'No items for these filters.')])
                    ),

                    hasMore && React.createElement('div', { className: 'mt-6' },
                        React.createElement('button', {
                            className: 'rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-60',
                            onClick: () => fetchFeed(type, q, page + 1, true),
                            disabled: loading,
                            type: 'button',
                        }, loading ? 'Loading...' : 'Load more')
                    )
                )
            );
        };

        createRoot(document.getElementById('react-feed-app')).render(React.createElement(App));
    </script>
</body>
</html>
