<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CB Market Hub React Frontend</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --bg: #0a1020;
            --bg-soft: #121a2d;
            --panel: rgba(17, 24, 39, 0.9);
            --panel-border: rgba(99, 102, 241, 0.24);
            --text: #e5e7eb;
            --text-soft: #9ca3af;
            --heading: #f8fafc;
            --brand: #6366f1;
            --brand-2: #8b5cf6;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; }
        body.react-shell {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 50% -20%, #253b83 0%, #121a2d 35%, #0a1020 100%);
            min-height: 100vh;
        }

        .container { width: min(1120px, 92vw); margin: 0 auto; }
        .topbar {
            position: sticky; top: 0; z-index: 30;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(10, 16, 32, 0.92);
            backdrop-filter: blur(8px);
        }
        .topbar-inner { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 0; flex-wrap: wrap; }
        .brand { color: var(--heading); text-decoration: none; font-weight: 700; letter-spacing: .2px; }
        .nav { display: flex; gap: 8px; flex-wrap: wrap; }
        .pill {
            text-decoration: none; color: var(--text); font-size: 13px; font-weight: 600;
            border: 1px solid rgba(148, 163, 184, 0.25); padding: 8px 12px; border-radius: 10px;
            background: rgba(15, 23, 42, 0.7);
        }
        .pill.active { color: white; border: none; background: linear-gradient(135deg, var(--brand), var(--brand-2)); box-shadow: 0 8px 24px rgba(99, 102, 241, .35); }

        .main { padding: 34px 0 48px; }
        .hero, .panel {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            box-shadow: 0 14px 36px rgba(2, 6, 23, .35);
        }
        .hero { padding: 24px; }
        .eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: .18em; color: #a5b4fc; font-weight: 700; }
        h1 { margin: 8px 0 0; color: var(--heading); font-size: clamp(1.7rem, 2.8vw, 2.2rem); }
        .hero-copy { margin: 10px 0 0; max-width: 760px; color: #cbd5e1; line-height: 1.45; font-size: 14px; }

        .grid-3, .grid-5, .grid-2 { display: grid; gap: 12px; }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 16px; }
        .grid-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .stat {
            border-radius: 14px; border: 1px solid rgba(148,163,184,.2);
            background: rgba(15, 23, 42, 0.7); padding: 14px;
        }
        .stat-label { margin: 0; color: var(--text-soft); font-size: 11px; letter-spacing: .14em; text-transform: uppercase; }
        .stat-value { margin: 7px 0 0; color: white; font-size: 1.45rem; font-weight: 700; }
        .stat-hint { margin: 4px 0 0; color: #94a3b8; font-size: 12px; }

        .section { margin-top: 16px; padding: 18px; }
        .section-head { display:flex; justify-content:space-between; gap:8px; align-items:center; margin-bottom:12px; }
        .section-title { margin: 0; color: var(--heading); font-size: 1rem; }
        .btn {
            border: 1px solid rgba(148, 163, 184, 0.28); background: rgba(30,41,59,.65); color: var(--text);
            border-radius: 10px; padding: 8px 12px; cursor: pointer; font-size: 12px; font-weight: 700;
        }
        .btn-primary {
            border: none; color: white;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
        }

        .form-grid { display:grid; grid-template-columns: 1fr 2fr auto; gap: 10px; }
        .field {
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(15,23,42,.8); color: var(--text);
            border-radius: 10px; padding: 10px 12px; font-size: 14px;
        }

        .feed-item {
            border-radius: 14px; border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15,23,42,.72); padding: 14px;
        }
        .feed-type { margin: 0; color: #a5b4fc; font-size: 11px; text-transform: uppercase; letter-spacing: .14em; }
        .feed-title { margin: 8px 0 0; color: #fff; font-size: 16px; }
        .feed-copy { margin: 8px 0 0; color: #cbd5e1; font-size: 14px; }
        .feed-meta { margin: 10px 0 0; color: #94a3b8; font-size: 12px; }

        .message { border-radius: 10px; padding: 10px 12px; font-size: 13px; }
        .message.error { border: 1px solid rgba(239,68,68,.5); background: rgba(127,29,29,.35); color: #fecaca; }
        .message.muted { border: 1px solid rgba(148,163,184,.22); background: rgba(30,41,59,.5); color: #cbd5e1; }

        @media (max-width: 1024px) { .grid-5 { grid-template-columns: repeat(3, minmax(0,1fr)); } }
        @media (max-width: 768px) {
            .grid-3, .grid-5, .grid-2 { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="react-shell">
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
            if (!res.ok) throw new Error(`Request failed: ${url}`);
            return res.json();
        });

        const StatCard = ({ label, value, hint }) => React.createElement('article', { className: 'stat' },
            React.createElement('p', { className: 'stat-label' }, label),
            React.createElement('p', { className: 'stat-value' }, value),
            hint && React.createElement('p', { className: 'stat-hint' }, hint)
        );

        const FeedItem = ({ item }) => React.createElement('article', { className: 'feed-item' },
            React.createElement('p', { className: 'feed-type' }, prettyType(item.type || 'item')),
            React.createElement('h3', { className: 'feed-title' }, item.title || '(untitled)'),
            React.createElement('p', { className: 'feed-copy' }, item.excerpt || 'No preview available.'),
            React.createElement('p', { className: 'feed-meta' }, `By ${item.author || 'Unknown'} • ${item.created_at ? new Date(item.created_at).toLocaleString() : 'Unknown time'}`)
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
                if (nextType && nextType !== 'all') query.set('type', nextType);
                if (nextQ.trim()) query.set('q', nextQ.trim());

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

            return React.createElement('main', null,
                React.createElement('header', { className: 'topbar' },
                    React.createElement('div', { className: 'container topbar-inner' },
                        React.createElement('a', { href: '/', className: 'brand' }, 'CB Market Hub'),
                        React.createElement('nav', { className: 'nav' },
                            ...DASHBOARD_LINKS.map((item) => React.createElement('a', {
                                key: item.label,
                                href: item.href,
                                className: `pill ${item.href === '/labs/react' ? 'active' : ''}`,
                            }, item.label))
                        )
                    )
                ),

                React.createElement('div', { className: 'container main' },
                    React.createElement('section', { className: 'hero' },
                        React.createElement('p', { className: 'eyebrow' }, 'React Launchpad'),
                        React.createElement('h1', null, 'Enterprise-ready React experience'),
                        React.createElement('p', { className: 'hero-copy' }, 'A visibly styled and fully wired React page with clear loading, empty, and error states. This view is connected to live Laravel API endpoints and keeps URL filter state in sync.'),
                        error && React.createElement('p', { className: 'message error', style: { marginTop: '12px' } }, error),
                        React.createElement('div', { className: 'grid-3' },
                            React.createElement(StatCard, { label: 'Runtime', value: 'React 18', hint: 'CDN module load' }),
                            React.createElement(StatCard, { label: 'Styling', value: 'Dedicated CSS shell', hint: 'Not dependent on utility extraction' }),
                            React.createElement(StatCard, { label: 'API wiring', value: 'Live', hint: '/labs/react/* endpoints' }),
                        )
                    ),

                    React.createElement('section', { className: 'panel section' },
                        React.createElement('div', { className: 'section-head' },
                            React.createElement('h2', { className: 'section-title' }, 'Live backend summary'),
                            React.createElement('button', {
                                type: 'button',
                                className: 'btn',
                                onClick: () => {
                                    setError('');
                                    loadSummary();
                                },
                            }, loadingSummary ? 'Refreshing...' : 'Refresh')
                        ),
                        React.createElement('div', { className: 'grid-5' },
                            ...(loadingSummary && !summary
                                ? new Array(5).fill(null).map((_, idx) => React.createElement('div', { key: `summary-loading-${idx}`, className: 'message muted' }, 'Loading...'))
                                : summaryCards.map((card) => React.createElement(StatCard, { key: card.label, ...card })))
                        )
                    ),

                    React.createElement('section', { className: 'panel section' },
                        React.createElement('h2', { className: 'section-title' }, 'Live feed preview'),
                        React.createElement('form', {
                            className: 'form-grid',
                            style: { marginTop: '12px' },
                            onSubmit: (event) => {
                                event.preventDefault();
                                setError('');
                                loadFeed(type, q);
                            },
                        },
                            React.createElement('select', {
                                className: 'field',
                                value: type,
                                onChange: (e) => setType(e.target.value),
                            }, ...TYPE_OPTIONS.map((option) => React.createElement('option', { key: option.value, value: option.value }, option.label))),
                            React.createElement('input', {
                                className: 'field',
                                type: 'text',
                                value: q,
                                placeholder: 'Search title/body/comments...',
                                onChange: (e) => setQ(e.target.value),
                            }),
                            React.createElement('button', { className: 'btn btn-primary', type: 'submit' }, loadingFeed ? 'Applying...' : 'Apply filters')
                        ),
                        React.createElement('div', { className: 'grid-2', style: { marginTop: '12px' } },
                            ...(loadingFeed && feedItems.length === 0
                                ? [React.createElement('p', { key: 'loading-feed', className: 'message muted', style: { gridColumn: '1 / -1' } }, 'Loading feed preview...')]
                                : feedItems.length
                                    ? feedItems.map((item, index) => React.createElement(FeedItem, { key: `${item.type}-${index}`, item }))
                                    : [React.createElement('p', { key: 'empty', className: 'message muted', style: { gridColumn: '1 / -1' } }, 'No feed items match these filters yet.')])
                        )
                    )
                )
            );
        };

        createRoot(document.getElementById('react-app')).render(React.createElement(App));
    </script>
</body>
</html>
