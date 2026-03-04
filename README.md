# CB Market Hub V1

CB Market Hub is a Laravel-based community platform for CB radio users. It combines social posting, buy/sell listings, events, polls, suggestions, and chat in one place.

## Core Features

- **Community Feed** with mixed activity (posts, polls, events, suggestions, listings, comments)
- **Posts** with optional anonymous posting and image uploads
- **Polls** with voting, comments, and visibility controls
- **Marketplace** for buy/sell/trade listings
- **Events** with image uploads and RSVP
- **Suggestions** with voting and status updates
- **Realtime-style Community Chat** (fetch + send flow)
- **Reactions** across multiple content types
- **Reporting/Moderation primitives** for flagged content
- **Notifications** with mark-all and mark-single-as-read

## Tech Stack

- PHP / Laravel
- Blade templates + Tailwind CSS
- SQLite/MySQL compatible migrations
- PHPUnit feature testing

## Quick Start

### 1) Install dependencies

```bash
composer install
npm install
```

### 2) Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Set your DB config in `.env` (SQLite is easiest for local dev).

### 3) Migrate and seed

```bash
php artisan migrate --seed
```

### 4) Start the app

```bash
php artisan serve
npm run dev
```

Then open `http://127.0.0.1:8000`.

## Test Suite

Run tests with:

```bash
php artisan test
```

Useful targeted runs:

```bash
php artisan test --filter=ReactionFeatureTest
php artisan test --filter=EventImageTest
php artisan test --filter=MarketplacePostFeedVisibilityTest
```

## Roles and Access

- Any authenticated user can create content and react/comment where enabled.
- Users with `is_admin = true` can access moderation queue routes.

## High-Level Route Areas

- `/` — community feed
- `/posts` — posts
- `/polls` — polls
- `/marketplace` — listings
- `/events` — events
- `/suggestions` — suggestions
- `/chat` — chat
- `/notifications` — user notifications
- `/moderation` — admin moderation queue

## Notes

- Some default Laravel files/components remain from the starter scaffold.
- Public file uploads are expected on the `public` disk (`storage:link` as needed).

## Blade-First Optimization Roadmap

The current app is optimized around Laravel + Blade, and that is the recommended direction for stability and speed of delivery.

### Backend performance priorities

1. Add short-lived cache layers for high-traffic feed queries.
2. Keep eager-loading on feed/content pages to avoid N+1 queries.
3. Add/verify indexes for fields frequently filtered or sorted (`created_at`, visibility/status columns).
4. Add targeted feature tests for hot paths (feed filtering, notifications, moderation actions).

### Blade UI priorities

1. Continue using reusable Blade components for repeated UI blocks (cards, metadata rows, action bars).
2. Use Alpine.js only for light progressive enhancement where needed.
3. Keep all primary routes server-rendered for reliability and SEO.
4. Iterate in small, testable visual improvements to forms, feed filters, and empty states.

