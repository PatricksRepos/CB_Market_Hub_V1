<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Suggestion;
use App\Support\Reactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    /** @var array<string, class-string<\Illuminate\Database\Eloquent\Model>> */
    private const REACTABLE_MODELS = [
        'post' => Post::class,
        'event' => Event::class,
        'listing' => Listing::class,
        'poll' => Poll::class,
        'poll_comment' => PollComment::class,
        'suggestion' => Suggestion::class,
    ];

    public function store(Request $request): RedirectResponse
    {
        if (!Reactions::isEnabled()) {
            return back()->with('status', 'Reactions are not available yet. Please run migrations.');
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::REACTABLE_MODELS))],
            'id' => ['required', 'integer', 'min:1'],
            'emoji' => ['required', Rule::in(Reaction::ALLOWED_EMOJIS)],
        ]);

        $modelClass = self::REACTABLE_MODELS[$data['type']];
        $reactable = $modelClass::query()->findOrFail($data['id']);

        $existingReaction = Reaction::query()
            ->where('user_id', $request->user()->id)
            ->where('reactable_type', $reactable->getMorphClass())
            ->where('reactable_id', $reactable->getKey())
            ->first();

        if ($existingReaction && $existingReaction->emoji === $data['emoji']) {
            $existingReaction->delete();

            return back()->with('status', 'Reaction removed.');
        }

        if ($existingReaction) {
            $existingReaction->update(['emoji' => $data['emoji']]);

            return back()->with('status', 'Reaction updated.');
        }

        $reactable->reactions()->create([
            'user_id' => $request->user()->id,
            'emoji' => $data['emoji'],
        ]);

        return back()->with('status', 'Reaction saved.');
    }
}
