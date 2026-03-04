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

        const typeStyles = {
            post: 'bg-sky-500/20 text-sky-200 border-sky-400/30',
            poll: 'bg-violet-500/20 text-violet-200 border-violet-400/30',
            event: 'bg-amber-500/20 text-amber-200 border-amber-400/30',
            suggestion: 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30',
            listing: 'bg-fuchsia-500/20 text-fuchsia-200 border-fuchsia-400/30',
            post_comment: 'bg-cyan-500/20 text-cyan-200 border-cyan-400/30',
            poll_comment: 'bg-pink-500/20 text-pink-200 border-pink-400/30',
        };

        const FeedCard = ({ item }) => {
            const pillClass = typeStyles[item.type] || 'bg-slate-700 text-slate-200 border-slate-500/40';

            return React.createElement('article', {
                className: 'group rounded-2xl border border-slate-800/80 bg-slate-900/80 p-5 shadow-xl shadow-slate-950/40 backdrop-blur transition hover:-translate-y-1 hover:border-indigo-400/50',
            },
                React.createElement('div', { className: 'flex items-center justify-between gap-2' },
                    React.createElement('span', { className: `rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide ${pillClass}` }, item.type.replace('_', ' ')),
                    React.createElement('span', { className: 'text-xs text-slate-400' }, item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time')
                ),
                React.createElement('h3', { className: 'mt-3 text-lg font-semibold text-white group-hover:text-indigo-200' }, item.title || '(untitled)'),
                React.createElement('p', { className: 'mt-2 text-sm leading-6 text-slate-300' }, item.excerpt || ''),
                React.createElement('div', { className: 'mt-4 flex items-center justify-between text-xs text-slate-400' },
                    React.createElement('span', null, `Author: ${item.author || 'Unknown'}`),
                    React.createElement('a', { href: '/labs/feed-react', className: 'text-indigo-300 hover:text-indigo-200' }, 'View details')
                )
            );
        };

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

            return React.createElement('main', { className: 'min-h-screen bg-[radial-gradient(circle_at_top,_#312e81_0%,_#0b1120_40%,_#020617_100%)]' },
                React.createElement('header', { className: 'sticky top-0 z-20 border-b border-slate-700/60 bg-slate-950/85 backdrop-blur' },
                    React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-4' },
                        React.createElement('div', { className: 'flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between' },
                            React.createElement('a', { href: '/', className: 'text-lg font-bold text-white' }, 'CB Community'),
                            React.createElement('nav', { className: 'flex flex-wrap gap-2' },
                                ...primaryNav.map((item) => React.createElement('a', {
                                    key: item.label,
                                    href: item.href,
                                    className: item.active
                                        ? 'rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-1.5 text-sm font-semibold text-white shadow'
                                        : 'rounded-lg border border-slate-700/80 bg-slate-900/80 px-3 py-1.5 text-sm text-slate-200 hover:border-indigo-400/50 hover:text-white',
                                }, item.label))
                            )
                        )
                    )
                ),

                React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-10' },
                    React.createElement('div', { className: 'mb-7 rounded-3xl border border-indigo-400/20 bg-slate-900/70 p-6 shadow-2xl shadow-indigo-950/30 backdrop-blur md:p-8' },
                        React.createElement('p', { className: 'text-xs font-semibold uppercase tracking-[0.15em] text-indigo-300' }, 'React Feed'),
                        React.createElement('h1', { className: 'mt-2 text-3xl font-bold text-white md:text-4xl' }, 'Community Feed in React'),
                        React.createElement('p', { className: 'mt-2 text-sm text-slate-300' }, 'Styled and wired for a cleaner, more app-like experience while Blade stays untouched.'),
                        React.createElement('div', { className: 'mt-5 flex flex-wrap gap-2' },
                            React.createElement('span', { className: 'rounded-full border border-slate-600/70 bg-slate-800/80 px-3 py-1 text-xs text-slate-200' }, `Loaded: ${items.length}`),
                            React.createElement('span', { className: 'rounded-full border border-slate-600/70 bg-slate-800/80 px-3 py-1 text-xs text-slate-200' }, `Total: ${total}`),
                            React.createElement('span', { className: 'rounded-full border border-slate-600/70 bg-slate-800/80 px-3 py-1 text-xs text-slate-200' }, `Page: ${page}`)
                        )
                    ),

                    React.createElement('form', {
                        className: 'rounded-2xl border border-slate-700/70 bg-slate-900/75 p-4 shadow-xl shadow-black/25 md:p-5',
                        onSubmit: (e) => {
                            e.preventDefault();
                            fetchFeed(type, q, 1, false);
                        },
                    },
                        React.createElement('div', { className: 'grid gap-3 md:grid-cols-4' },
                            React.createElement('select', {
                                className: 'rounded-xl border border-slate-600 bg-slate-800/90 px-3 py-2.5 text-sm text-slate-100 outline-none ring-indigo-400 focus:ring-2',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            }, ...typeOptions.map((opt) => React.createElement('option', { key: opt.value, value: opt.value }, opt.label))),
                            React.createElement('input', {
                                className: 'rounded-xl border border-slate-600 bg-slate-800/90 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-400 outline-none ring-indigo-400 focus:ring-2 md:col-span-2',
                                value: q,
                                placeholder: 'Search feed...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', {
                                type: 'submit',
                                className: 'rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-2.5 text-sm font-semibold text-white hover:opacity-95',
                            }, loading ? 'Loading...' : 'Apply filters')
                        )
                    ),

                    error && React.createElement('p', { className: 'mt-4 rounded-lg border border-red-700/80 bg-red-950/40 px-3 py-2 text-sm text-red-200' }, error),

                    React.createElement('section', { className: 'mt-6 grid gap-4 md:grid-cols-2' },
                        ...(loading && items.length === 0
                            ? [React.createElement('p', { key: 'loading', className: 'rounded-xl border border-slate-700/60 bg-slate-900/70 p-4 text-sm text-slate-300' }, 'Loading feed...')]
                            : items.length
                                ? items.map((item, i) => React.createElement(FeedCard, { key: `${item.type}-${i}`, item }))
                                : [React.createElement('p', { key: 'empty', className: 'rounded-xl border border-slate-700/60 bg-slate-900/70 p-4 text-sm text-slate-300' }, 'No items for these filters.')])
                    ),

                    hasMore && React.createElement('div', { className: 'mt-7 flex justify-center' },
                        React.createElement('button', {
                            className: 'rounded-xl border border-slate-600 bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-100 hover:border-indigo-400/60 hover:bg-slate-700 disabled:opacity-60',
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
