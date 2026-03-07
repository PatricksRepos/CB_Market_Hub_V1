<?php

namespace App\Support;

use App\Models\Badge;
use App\Models\PointTransaction;
use App\Models\User;
use App\Notifications\SimpleNotification;
use Illuminate\Support\Facades\DB;

class Gamification
{
    /** @var array<string, int> */
    public const ACTION_POINTS = [
        'post.created' => 10,
        'post.comment.created' => 2,
        'poll.created' => 8,
        'poll.vote.created' => 1,
        'poll.comment.created' => 2,
        'listing.created' => 6,
        'event.created' => 8,
        'suggestion.created' => 6,
        'reaction.received' => 1,
    ];

    public static function pointsFor(string $action): int
    {
        return self::ACTION_POINTS[$action] ?? 0;
    }

    public static function award(User $user, string $action, ?string $subjectKey = null, array $meta = []): bool
    {
        $points = self::pointsFor($action);

        if ($points === 0) {
            return false;
        }

        return DB::transaction(function () use ($user, $action, $subjectKey, $meta, $points) {
            $tx = PointTransaction::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'action' => $action,
                    'subject_key' => $subjectKey,
                ],
                [
                    'points' => $points,
                    'meta' => $meta,
                ],
            );

            if (!$tx->wasRecentlyCreated) {
                return false;
            }

            $freshUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $freshUser->points_total += $points;
            $freshUser->save();

            self::syncBadges($freshUser);

            return true;
        });
    }

    public static function syncBadges(User $user): void
    {
        self::ensureDefaultBadges();

        $eligibleBadges = Badge::query()
            ->where('points_required', '<=', $user->points_total)
            ->orderBy('points_required')
            ->get();

        foreach ($eligibleBadges as $badge) {
            $attached = $user->badges()->syncWithoutDetaching([
                $badge->id => ['earned_at' => now()],
            ]);

            if (!empty($attached['attached'])) {
                $user->notify(new SimpleNotification(
                    'New badge unlocked: '.$badge->name,
                    route('profiles.show', $user),
                    $badge->description ?? 'Keep contributing to unlock more badges.'
                ));
            }
        }

        $topBadge = $eligibleBadges->last();
        $user->current_badge_slug = $topBadge?->slug;
        $user->save();
    }

    public static function ensureDefaultBadges(): void
    {
        $defaults = [
            ['slug' => 'newcomer', 'name' => 'Newcomer', 'description' => 'Joined the community and started participating.', 'points_required' => 0, 'sort_order' => 1],
            ['slug' => 'contributor', 'name' => 'Contributor', 'description' => 'Earned 50 points by contributing useful content.', 'points_required' => 50, 'sort_order' => 2],
            ['slug' => 'trusted-voice', 'name' => 'Trusted Voice', 'description' => 'Earned 150 points and became a reliable voice.', 'points_required' => 150, 'sort_order' => 3],
            ['slug' => 'community-pillar', 'name' => 'Community Pillar', 'description' => 'Earned 350 points through consistent contribution.', 'points_required' => 350, 'sort_order' => 4],
        ];

        foreach ($defaults as $badge) {
            Badge::query()->updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
