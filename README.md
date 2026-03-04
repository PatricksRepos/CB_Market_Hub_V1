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
