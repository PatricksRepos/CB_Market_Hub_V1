<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React Frontend</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-950 text-slate-100">
    <div id="react-app"></div>

    <script type="module">
        import React, { useEffect, useMemo, useState } from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const TYPE_OPTIONS = [
            { value: 'all', label: 'All types' },
            { value: 'post', label: 'Posts' },
            { value: 'poll', label: 'Polls' },
            { value: 'event', label: 'Events' },
            { value: 'suggestion', label: 'Suggestions' },
            { value: 'listing', label: 'Listings' },
            { value: 'post_comment', label: 'Post comments' },
            { value: 'poll_comment', label: 'Poll comments' },
        ];

        const DASHBOARD_LINKS = [
            { label: 'React Home', href: '/labs/react' },
            { label: 'Feed App', href: '/labs/feed-react' },
            { label: 'Full Site App', href: '/labs/app-react' },
            { label: 'Classic Feed', href: '/' },
        ];

        const prettyType = (type) => type.replaceAll('_', ' ');

        const fetchJson = (url) => fetch(url).then((res) => {
            if (!res.ok) {
                throw new Error(`Request failed: ${url}`);
            }

            return res.json();
        });

        const StatCard = ({ label, value, hint }) => React.createElement(
            'article',
            { className: 'rounded-2xl border border-indigo-400/20 bg-slate-900/75 p-4 shadow-lg shadow-indigo-950/20' },
            React.createElement('p', { className: 'text-xs uppercase tracking-[0.15em] text-slate-400' }, label),
            React.createElement('p', { className: 'mt-2 text-2xl font-semibold text-white' }, value),
            hint && React.createElement('p', { className: 'mt-1 text-xs text-slate-500' }, hint)
        );

        const FeedItem = ({ item }) => React.createElement(
            'article',
            { className: 'rounded-2xl border border-slate-700/80 bg-slate-900/85 p-4 shadow-xl shadow-black/20 transition hover:-translate-y-0.5 hover:border-indigo-400/60' },
            React.createElement('p', { className: 'text-xs uppercase tracking-[0.14em] text-indigo-300' }, prettyType(item.type || 'item')),
            React.createElement('h3', { className: 'mt-2 text-base font-semibold text-white' }, item.title || '(untitled)'),
            React.createElement('p', { className: 'mt-2 text-sm text-slate-300' }, item.excerpt || 'No preview available.'),
            React.createElement('p', { className: 'mt-3 text-xs text-slate-400' }, `By ${item.author || 'Unknown'} • ${item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time'}`)
        );

        const App = () => {
            const params = new URLSearchParams(window.location.search);
            const [summary, setSummary] = useState(null);
            const [feedItems, setFeedItems] = useState([]);
            const [type, setType] = useState(params.get('type') || 'all');
            const [q, setQ] = useState(params.get('q') || '');
            const [loadingSummary, setLoadingSummary] = useState(true);
            const [loadingFeed, setLoadingFeed] = useState(true);
            const [error, setError] = useState('');

            const summaryCards = useMemo(() => [
                { label: 'Posts', value: summary ? String(summary.posts) : '—' },
                { label: 'Polls', value: summary ? String(summary.polls) : '—' },
                { label: 'Events', value: summary ? String(summary.events) : '—' },
                { label: 'Suggestions', value: summary ? String(summary.suggestions) : '—' },
                { label: 'Listings', value: summary ? String(summary.listings) : '—' },
            ], [summary]);

            const syncUrl = (nextType, nextQ) => {
                const query = new URLSearchParams();

                if (nextType && nextType !== 'all') {
                    query.set('type', nextType);
                }

                if (nextQ.trim()) {
                    query.set('q', nextQ.trim());
                }

                const queryString = query.toString();
                window.history.replaceState({}, '', queryString ? `/labs/react?${queryString}` : '/labs/react');

                return queryString;
            };

            const loadSummary = () => {
                setLoadingSummary(true);

                return fetchJson('/labs/react/summary')
                    .then(setSummary)
                    .catch(() => setError('Could not load summary data right now.'))
                    .finally(() => setLoadingSummary(false));
            };

            const loadFeed = (nextType, nextQ) => {
                setLoadingFeed(true);
                const queryString = syncUrl(nextType, nextQ);
                const url = queryString ? `/labs/react/feed?${queryString}` : '/labs/react/feed';

                return fetchJson(url)
                    .then((feedData) => setFeedItems(feedData.items || []))
                    .catch(() => setError('Could not load feed data right now.'))
                    .finally(() => setLoadingFeed(false));
            };

            useEffect(() => {
                setError('');
                loadSummary();
                loadFeed(type, q);
            }, []);

            const onApplyFilters = (event) => {
                event.preventDefault();
                setError('');
                loadFeed(type, q);
            };

            return React.createElement(
                'main',
                { className: 'min-h-screen bg-[radial-gradient(circle_at_top,_#1d4ed8_0%,_#0f172a_38%,_#020617_100%)]' },
                React.createElement('header', { className: 'sticky top-0 z-20 border-b border-slate-800/80 bg-slate-950/90 backdrop-blur' },
                    React.createElement('div', { className: 'mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-6 py-4' },
                        React.createElement('a', { href: '/', className: 'text-lg font-bold text-white' }, 'CB Market Hub'),
                        React.createElement('nav', { className: 'flex flex-wrap gap-2' },
                            ...DASHBOARD_LINKS.map((item) => React.createElement('a', {
                                key: item.label,
                                href: item.href,
                                className: item.href === '/labs/react'
                                    ? 'rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-1.5 text-sm font-semibold text-white shadow'
                                    : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-indigo-400/60 hover:text-white',
                            }, item.label))
                        )
                    )
                ),
                React.createElement('div', { className: 'mx-auto max-w-6xl px-6 py-10' },
                    React.createElement('section', { className: 'rounded-3xl border border-indigo-400/20 bg-slate-900/75 p-6 shadow-2xl shadow-indigo-950/25 md:p-8' },
                        React.createElement('p', { className: 'text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300' }, 'React Launchpad'),
                        React.createElement('h1', { className: 'mt-2 text-3xl font-bold text-white md:text-4xl' }, 'Enterprise-ready React experience'),
                        React.createElement('p', { className: 'mt-3 max-w-3xl text-sm text-slate-300' }, 'This screen is fully wired to Laravel summary and feed APIs with polished layout, resilient loading states, and URL-synced filters for a production-style experience.'),
                        error && React.createElement('p', { className: 'mt-4 rounded-lg border border-red-700/80 bg-red-950/40 px-3 py-2 text-sm text-red-200' }, error),
                        React.createElement('div', { className: 'mt-6 grid gap-3 sm:grid-cols-3' },
                            React.createElement(StatCard, { label: 'Runtime', value: 'React 18', hint: 'CDN-based module load' }),
                            React.createElement(StatCard, { label: 'Design System', value: 'Tailwind CSS', hint: 'Tokenized utility classes' }),
                            React.createElement(StatCard, { label: 'API Wiring', value: 'Live', hint: '/labs/react/* endpoints' })
                        )
                    ),

                    React.createElement('section', { className: 'mt-6 rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5' },
                        React.createElement('div', { className: 'mb-4 flex items-center justify-between gap-2' },
                            React.createElement('h2', { className: 'text-lg font-semibold text-white' }, 'Live backend summary'),
                            React.createElement('button', {
                                type: 'button',
                                className: 'rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-indigo-400/60 hover:text-white',
                                onClick: () => {
                                    setError('');
                                    loadSummary();
                                },
                            }, loadingSummary ? 'Refreshing…' : 'Refresh')
                        ),
                        React.createElement('div', { className: 'grid gap-3 sm:grid-cols-2 lg:grid-cols-5' },
                            ...(loadingSummary && !summary
                                ? new Array(5).fill(null).map((_, index) => React.createElement('div', {
                                    key: `skeleton-${index}`,
                                    className: 'h-24 animate-pulse rounded-2xl border border-slate-700/60 bg-slate-800/70',
                                }))
                                : summaryCards.map((card) => React.createElement(StatCard, { key: card.label, ...card }))
                            )
                        )
                    ),

                    React.createElement('section', { className: 'mt-6 rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5' },
                        React.createElement('h2', { className: 'text-lg font-semibold text-white' }, 'Live feed preview'),
                        React.createElement('form', { className: 'mt-4 grid gap-3 md:grid-cols-4', onSubmit: onApplyFilters },
                            React.createElement('select', {
                                className: 'rounded-xl border border-slate-600 bg-slate-800/90 px-3 py-2.5 text-sm text-slate-100 outline-none ring-indigo-400 focus:ring-2',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            }, ...TYPE_OPTIONS.map((option) => React.createElement('option', { key: option.value, value: option.value }, option.label))),
                            React.createElement('input', {
                                className: 'rounded-xl border border-slate-600 bg-slate-800/90 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-400 outline-none ring-indigo-400 focus:ring-2 md:col-span-2',
                                type: 'text',
                                value: q,
                                placeholder: 'Search title/body/comments...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', {
                                className: 'rounded-xl bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-2.5 text-sm font-semibold text-white hover:opacity-95',
                                type: 'submit',
                            }, loadingFeed ? 'Applying…' : 'Apply filters')
                        ),
                        React.createElement('div', { className: 'mt-4 grid gap-4 md:grid-cols-2' },
                            ...(loadingFeed && feedItems.length === 0
                                ? [React.createElement('p', { key: 'loading-feed', className: 'rounded-xl border border-slate-700/60 bg-slate-800/70 p-4 text-sm text-slate-300 md:col-span-2' }, 'Loading feed preview...')]
                                : feedItems.length
                                    ? feedItems.map((item, index) => React.createElement(FeedItem, { key: `${item.type}-${index}`, item }))
                                    : [React.createElement('p', { key: 'empty', className: 'rounded-xl border border-slate-700/60 bg-slate-800/70 p-4 text-sm text-slate-300 md:col-span-2' }, 'No feed items match these filters yet.')])
                        )
                    )
                )
            );
        };

        createRoot(document.getElementById('react-app')).render(React.createElement(App));
    </script>
</body>
</html>
