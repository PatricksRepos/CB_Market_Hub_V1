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
        import React, { useEffect, useMemo, useState } from 'https://esm.sh/react@18.3.1';
        import { createRoot } from 'https://esm.sh/react-dom@18.3.1/client';

        const API = {
            overview: '/labs/react/site-overview',
            summary: '/labs/react/summary',
            feed: '/labs/react/feed?per_page=6',
        };

        const sectionMeta = {
            feed: { label: 'Feed', href: '/labs/feed-react' },
            posts: { label: 'Posts', href: '/posts' },
            polls: { label: 'Polls', href: '/polls' },
            marketplace: { label: 'Marketplace', href: '/marketplace' },
            events: { label: 'Events', href: '/events' },
            suggestions: { label: 'Suggestions', href: '/suggestions' },
            chat: { label: 'Chat', href: '/chat' },
        };

        const sectionOrder = ['posts', 'polls', 'marketplace', 'events', 'suggestions', 'chat'];

        const fetchJson = (url) => fetch(url).then((res) => {
            if (!res.ok) throw new Error(`Failed request: ${url}`);
            return res.json();
        });

        const DataCard = ({ title, value, hint }) => React.createElement('div', {
            className: 'rounded-xl border border-indigo-400/20 bg-slate-900/75 px-4 py-3 shadow-lg shadow-indigo-950/20',
        },
            React.createElement('p', { className: 'text-xs uppercase tracking-wide text-slate-400' }, title),
            React.createElement('p', { className: 'mt-1 text-2xl font-semibold text-white' }, String(value ?? 0)),
            hint && React.createElement('p', { className: 'mt-1 text-xs text-slate-500' }, hint)
        );

        const ListCard = ({ item }) => React.createElement('a', {
            href: item.href || '#',
            className: 'block rounded-xl border border-slate-700/80 bg-slate-900/85 p-4 transition hover:-translate-y-0.5 hover:border-indigo-400/60 hover:shadow-lg hover:shadow-indigo-950/30',
        },
            React.createElement('p', { className: 'text-sm font-semibold text-white' }, item.title || 'Untitled'),
            React.createElement('p', { className: 'mt-1 text-sm text-slate-300' }, item.excerpt || 'No preview available'),
            React.createElement('p', { className: 'mt-2 text-xs text-slate-400' }, `${item.author || 'Unknown'} • ${item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time'}`)
        );

        const App = () => {
            const [overview, setOverview] = useState({ counts: {}, sections: {} });
            const [summary, setSummary] = useState({});
            const [feedPreview, setFeedPreview] = useState([]);
            const [activeSection, setActiveSection] = useState('posts');
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState('');

            const hydrate = () => {
                setLoading(true);
                setError('');

                return Promise.allSettled([
                    fetchJson(API.overview),
                    fetchJson(API.summary),
                    fetchJson(API.feed),
                ])
                    .then(([overviewResult, summaryResult, feedResult]) => {
                        if (overviewResult.status === 'fulfilled') {
                            setOverview(overviewResult.value || { counts: {}, sections: {} });
                        }
                        if (summaryResult.status === 'fulfilled') {
                            setSummary(summaryResult.value || {});
                        }
                        if (feedResult.status === 'fulfilled') {
                            setFeedPreview(feedResult.value?.items || []);
                        }

                        if ([overviewResult, summaryResult, feedResult].every((r) => r.status === 'rejected')) {
                            setError('Could not load React app data right now.');
                        }
                    })
                    .finally(() => setLoading(false));
            };

            useEffect(() => {
                hydrate();
            }, []);

            const sectionItems = useMemo(() => overview.sections?.[activeSection] || [], [overview, activeSection]);

            return React.createElement('main', { className: 'min-h-screen bg-[radial-gradient(circle_at_top,_#1d4ed8_0%,_#0f172a_38%,_#020617_100%)]' },
                React.createElement('header', { className: 'sticky top-0 z-20 border-b border-slate-800/90 bg-slate-950/95 backdrop-blur' },
                    React.createElement('div', { className: 'mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-6 py-4' },
                        React.createElement('a', { href: '/', className: 'text-lg font-bold text-white' }, 'CB Community'),
                        React.createElement('nav', { className: 'flex flex-wrap gap-2' },
                            ...Object.entries(sectionMeta).map(([key, item]) => React.createElement('a', {
                                key,
                                href: item.href,
                                className: key === 'feed'
                                    ? 'rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-1.5 text-sm font-medium text-white'
                                    : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-indigo-400/50 hover:text-white',
                            }, item.label)),
                            React.createElement('a', { href: '/labs/app-react', className: 'rounded-lg border border-violet-400/60 bg-violet-500/20 px-3 py-1.5 text-sm font-medium text-violet-100' }, 'React App')
                        )
                    )
                ),

                React.createElement('div', { className: 'mx-auto grid max-w-7xl gap-6 px-6 py-8 lg:grid-cols-12' },
                    React.createElement('section', { className: 'rounded-3xl border border-indigo-400/20 bg-slate-900/75 p-6 shadow-2xl shadow-indigo-950/30 lg:col-span-8' },
                        React.createElement('div', { className: 'flex flex-wrap items-start justify-between gap-3' },
                            React.createElement('div', null,
                                React.createElement('p', { className: 'text-xs font-semibold uppercase tracking-[0.18em] text-indigo-300' }, 'React App Surface'),
                                React.createElement('h1', { className: 'mt-2 text-3xl font-bold text-white' }, 'React app shell (full-site preview)'),
                                React.createElement('p', { className: 'mt-2 text-sm text-slate-300' }, 'A structured React entrypoint for all major modules with stable data wiring, while Blade routes stay as fallback.')
                            ),
                            React.createElement('button', {
                                type: 'button',
                                onClick: hydrate,
                                className: 'rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-sm text-slate-100 hover:border-indigo-400/60 hover:bg-slate-700',
                            }, loading ? 'Refreshing…' : 'Refresh data')
                        ),

                        React.createElement('div', { className: 'mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4' },
                            React.createElement(DataCard, { title: 'Feed Items', value: overview.counts?.feed, hint: 'Combined activity stream' }),
                            React.createElement(DataCard, { title: 'Posts', value: summary.posts ?? overview.counts?.posts }),
                            React.createElement(DataCard, { title: 'Polls', value: summary.polls ?? overview.counts?.polls }),
                            React.createElement(DataCard, { title: 'Marketplace', value: summary.listings ?? overview.counts?.marketplace })
                        ),

                        error && React.createElement('p', { className: 'mt-4 rounded-lg border border-red-700 bg-red-950/30 p-3 text-sm text-red-200' }, error),

                        React.createElement('div', { className: 'mt-6 flex flex-wrap gap-2' },
                            ...sectionOrder.map((key) => React.createElement('button', {
                                key,
                                type: 'button',
                                onClick: () => setActiveSection(key),
                                className: key === activeSection
                                    ? 'rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-3 py-1.5 text-sm font-medium text-white'
                                    : 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm text-slate-200 hover:border-slate-500',
                            }, sectionMeta[key].label))
                        ),

                        React.createElement('div', { className: 'mt-4 space-y-3' },
                            ...(loading && sectionItems.length === 0
                                ? [React.createElement('p', { key: 'loading', className: 'text-sm text-slate-300' }, 'Loading module items...')]
                                : sectionItems.length
                                    ? sectionItems.map((item, idx) => React.createElement(ListCard, { key: `${activeSection}-${idx}`, item }))
                                    : [React.createElement('p', { key: 'empty', className: 'text-sm text-slate-400' }, 'No items available for this module yet.')])
                        )
                    ),

                    React.createElement('aside', { className: 'space-y-6 lg:col-span-4' },
                        React.createElement('section', { className: 'rounded-2xl border border-indigo-400/20 bg-slate-900/75 p-5' },
                            React.createElement('h2', { className: 'text-lg font-semibold text-white' }, 'Quick links'),
                            React.createElement('div', { className: 'mt-3 grid gap-2' },
                                ...Object.values(sectionMeta).map((item) => React.createElement('a', {
                                    key: item.label,
                                    href: item.href,
                                    className: 'rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200 hover:border-indigo-400/50 hover:text-white',
                                }, `Open ${item.label}`))
                            )
                        ),

                        React.createElement('section', { className: 'rounded-2xl border border-indigo-400/20 bg-slate-900/75 p-5' },
                            React.createElement('div', { className: 'mb-3 flex items-center justify-between' },
                                React.createElement('h2', { className: 'text-lg font-semibold text-white' }, 'Feed preview'),
                                React.createElement('a', { href: '/labs/feed-react', className: 'text-xs text-indigo-300 hover:text-indigo-200' }, 'Open full feed')
                            ),
                            React.createElement('div', { className: 'space-y-2' },
                                ...(feedPreview.length
                                    ? feedPreview.map((item, idx) => React.createElement('div', { key: `feed-${idx}`, className: 'rounded-lg border border-slate-800 bg-slate-900/90 p-3' },
                                        React.createElement('p', { className: 'text-xs uppercase tracking-wide text-indigo-300' }, item.type || 'item'),
                                        React.createElement('p', { className: 'mt-1 text-sm font-semibold text-white' }, item.title || 'Untitled'),
                                        React.createElement('p', { className: 'mt-1 text-xs text-slate-300' }, item.excerpt || ''),
                                    ))
                                    : [React.createElement('p', { key: 'feed-empty', className: 'text-sm text-slate-400' }, loading ? 'Loading feed preview...' : 'No feed data available.')])
                            )
                        )
                    )
                )
            );
        };

        createRoot(document.getElementById('react-site-app')).render(React.createElement(App));
    </script>
</body>
</html>
