<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React Feed</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-950 text-slate-100">
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

        const primaryNav = [
            { label: 'Feed', href: '/' },
            { label: 'Posts', href: '/posts' },
            { label: 'Polls', href: '/polls' },
            { label: 'Marketplace', href: '/marketplace' },
            { label: 'Events', href: '/events' },
            { label: 'Suggestions', href: '/suggestions' },
            { label: 'Chat', href: '/chat' },
            { label: 'React Feed', href: '/labs/feed-react', active: true },
        ];

        const accountNav = [
            { label: 'Notifications', href: '/notifications' },
            { label: 'Profile', href: '/profile' },
            { label: 'Log out', href: '/logout', danger: true },
        ];

        const quickActions = [
            { label: 'Add Post', href: '/posts/create' },
            { label: 'Add Event', href: '/events/create' },
            { label: 'Add Suggestion', href: '/suggestions/create' },
        ];

        const FeedCard = ({ item }) => React.createElement(
            'article',
            { className: 'group rounded-2xl border border-slate-800/80 bg-slate-900/90 p-5 shadow-xl shadow-slate-950/20 transition hover:-translate-y-0.5 hover:border-indigo-400/50' },
            React.createElement('div', { className: 'flex items-center justify-between gap-2' },
                React.createElement('span', { className: 'rounded-full bg-indigo-500/15 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-300' }, item.type.replace('_', ' ')),
                React.createElement('span', { className: 'text-xs text-slate-400' }, item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time'),
            ),
            React.createElement('h3', { className: 'mt-3 text-lg font-semibold text-white group-hover:text-indigo-200' }, item.title || '(untitled)'),
            React.createElement('p', { className: 'mt-2 text-sm leading-6 text-slate-300' }, item.excerpt || ''),
            React.createElement('p', { className: 'mt-3 text-xs font-medium text-slate-400' }, `Author: ${item.author || 'Unknown'}`),
        );

        const App = () => {
            const params = new URLSearchParams(window.location.search);
            const [type, setType] = useState(params.get('type') || 'all');
            const [q, setQ] = useState(params.get('q') || '');
            const [items, setItems] = useState([]);
            const [page, setPage] = useState(1);
            const [hasMore, setHasMore] = useState(false);
            const [total, setTotal] = useState(0);
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState('');

            const fetchFeed = (nextType, nextQ, nextPage, append = false) => {
                setLoading(true);
                setError('');
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
                        setTotal(Number(data.pagination?.total || 0));
                        setPage(nextPage);
                    })
                    .catch((err) => setError(err?.message || 'Failed to load feed items.'))
                    .finally(() => setLoading(false));
            };

            useEffect(() => {
                fetchFeed(type, q, 1, false);
            }, []);

            return React.createElement(
                'main',
                { className: 'min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950' },
                React.createElement('header', { className: 'sticky top-0 z-20 border-b border-slate-800/90 bg-slate-950/95 backdrop-blur' },
                    React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-4' },
                        React.createElement('div', { className: 'flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between' },
                            React.createElement('div', null,
                                React.createElement('a', { href: '/', className: 'text-lg font-bold text-white' }, 'CB Community'),
                                React.createElement('nav', { className: 'mt-3 flex flex-wrap gap-2' },
                                    ...primaryNav.map((item) => React.createElement('a', {
                                        key: item.label,
                                        href: item.href,
                                        className: item.active
                                            ? 'rounded-lg bg-indigo-600/90 px-3 py-1.5 text-sm font-medium text-white'
                                            : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-slate-500 hover:text-white',
                                    }, item.label))
                                )
                            ),
                            React.createElement('nav', { className: 'flex flex-wrap gap-2' },
                                ...accountNav.map((item) => React.createElement('a', {
                                    key: item.label,
                                    href: item.href,
                                    className: item.danger
                                        ? 'rounded-lg border border-rose-400/40 bg-rose-950/40 px-3 py-1.5 text-sm text-rose-200 hover:bg-rose-900/50'
                                        : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-slate-500 hover:text-white',
                                }, item.label))
                            )
                        )
                    )
                ),
                React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-12' },
                    React.createElement('div', { className: 'mb-7 rounded-3xl border border-slate-800/80 bg-slate-900/70 p-6 shadow-2xl shadow-black/20 backdrop-blur-sm md:p-8' },
                        React.createElement('div', { className: 'flex flex-col gap-4 md:flex-row md:items-center md:justify-between' },
                            React.createElement('div', null,
                                React.createElement('p', { className: 'text-xs font-semibold uppercase tracking-[0.15em] text-indigo-300' }, 'React Feed (Implementation Track)'),
                                React.createElement('h1', { className: 'mt-2 text-3xl font-bold text-white md:text-4xl' }, 'Community Feed in React'),
                                React.createElement('p', { className: 'mt-2 text-sm text-slate-300' }, 'Yes — we can style now. This screen is ready for visual refinement while staying isolated from Blade routes.')
                            ),
                            React.createElement('a', { href: '/', className: 'inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-slate-700' }, 'Open Blade feed')
                        ),
                        React.createElement('div', { className: 'mt-5 flex flex-wrap gap-2' },
                            React.createElement('span', { className: 'rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-slate-300' }, `Loaded: ${items.length}`),
                            React.createElement('span', { className: 'rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-slate-300' }, `Total matches: ${total}`),
                            React.createElement('span', { className: 'rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-slate-300' }, `Page: ${page}`)
                        ),
                        React.createElement('div', { className: 'mt-5 flex flex-wrap gap-2' },
                            ...quickActions.map((action) => React.createElement('a', {
                                key: action.label,
                                href: action.href,
                                className: 'rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-slate-100 hover:bg-white/20',
                            }, action.label))
                        )
                    ),

                    React.createElement('form', {
                        className: 'rounded-2xl border border-slate-800/80 bg-slate-900/80 p-4 shadow-xl shadow-black/20 md:p-5',
                        onSubmit: (e) => {
                            e.preventDefault();
                            setError('');
                            fetchFeed(type, q, 1, false);
                        },
                    },
                        React.createElement('div', { className: 'grid gap-3 md:grid-cols-4' },
                            React.createElement('select', {
                                className: 'rounded-xl border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 outline-none ring-indigo-400 focus:ring-2',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            }, ...typeOptions.map((opt) => React.createElement('option', { key: opt.value, value: opt.value }, opt.label))),
                            React.createElement('input', {
                                className: 'rounded-xl border border-slate-700 bg-slate-800 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-400 outline-none ring-indigo-400 focus:ring-2 md:col-span-2',
                                value: q,
                                placeholder: 'Search feed...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', {
                                type: 'submit',
                                className: 'rounded-xl bg-indigo-500 px-3 py-2.5 text-sm font-semibold text-white hover:bg-indigo-400',
                            }, 'Apply')
                        )
                    ),

                    error && React.createElement('p', { className: 'mt-4 rounded-lg border border-red-800 bg-red-950/40 px-3 py-2 text-sm text-red-300' }, error),

                    React.createElement('section', { className: 'mt-6 grid gap-4 md:grid-cols-2' },
                        ...(loading && items.length === 0
                            ? [React.createElement('p', { key: 'loading', className: 'text-sm text-slate-300' }, 'Loading feed...')]
                            : items.length
                                ? items.map((item, i) => React.createElement(FeedCard, { key: `${item.type}-${i}`, item }))
                                : [React.createElement('p', { key: 'empty', className: 'rounded-xl border border-slate-800 bg-slate-900/80 p-4 text-sm text-slate-300' }, 'No items for these filters.')])
                    ),

                    hasMore && React.createElement('div', { className: 'mt-7 flex justify-center' },
                        React.createElement('button', {
                            className: 'rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-100 hover:bg-slate-700 disabled:opacity-60',
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
