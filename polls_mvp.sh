#!/usr/bin/env bash
set -euo pipefail

bold(){ printf "\033[1m%s\033[0m\n" "$*"; }

bold "Creating models + migrations..."
php artisan make:model Poll -m >/dev/null || true
php artisan make:model PollOption -m >/dev/null || true
php artisan make:model PollVote -m >/dev/null || true

POLL_MIG="$(ls -1 database/migrations/*create_polls_table.php | tail -n 1)"
OPT_MIG="$(ls -1 database/migrations/*create_poll_options_table.php | tail -n 1)"
VOTE_MIG="$(ls -1 database/migrations/*create_poll_votes_table.php | tail -n 1)"

bold "Writing migrations..."
cat <<'PHP' > "$POLL_MIG"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('question', 200);
            $table->text('description')->nullable();

            // account required, but can display as anon
            $table->boolean('is_anonymous')->default(false);
            $table->string('anonymous_name', 60)->nullable();

            // immediate | after_end
            $table->string('results_visibility')->default('after_end')->index();

            // timing
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
PHP

cat <<'PHP' > "$OPT_MIG"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['poll_id','sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_options');
    }
};
PHP

cat <<'PHP' > "$VOTE_MIG"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // One vote per account per poll:
            $table->unique(['poll_id','user_id']);
            $table->index(['poll_id','poll_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
PHP

bold "Writing models..."
cat <<'PHP' > app/Models/Poll.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Poll extends Model
{
    protected $fillable = [
        'user_id','question','description',
        'is_anonymous','anonymous_name',
        'results_visibility','starts_at','ends_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function options(): HasMany { return $this->hasMany(PollOption::class)->orderBy('sort_order'); }
    public function votes(): HasMany { return $this->hasMany(PollVote::class); }

    public function isOpen(): bool
    {
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }

    public function resultsAreVisibleToUser(?User $user): bool
    {
        // If immediate: always visible
        if ($this->results_visibility === 'immediate') return true;

        // after_end: only visible once ended
        if ($this->ends_at && now()->gte($this->ends_at)) return true;

        // otherwise, user must vote first to see results? (your preference was “think for themselves”)
        // So: hide results until end, even if they've voted.
        return false;
    }
}
PHP

cat <<'PHP' > app/Models/PollOption.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $fillable = ['poll_id','label','sort_order'];

    public function poll(): BelongsTo { return $this->belongsTo(Poll::class); }
    public function votes(): HasMany { return $this->hasMany(PollVote::class); }
}
PHP

cat <<'PHP' > app/Models/PollVote.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    protected $fillable = ['poll_id','poll_option_id','user_id'];

    public function poll(): BelongsTo { return $this->belongsTo(Poll::class); }
    public function option(): BelongsTo { return $this->belongsTo(PollOption::class, 'poll_option_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
PHP

bold "Creating controllers..."
php artisan make:controller PollController >/dev/null || true
php artisan make:controller PollVoteController >/dev/null || true

cat <<'PHP' > app/Http/Controllers/PollController.php
<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::query()
            ->withCount('votes')
            ->latest()
            ->paginate(20);

        return view('polls.index', compact('polls'));
    }

    public function show(Poll $poll, Request $request)
    {
        $poll->load(['options', 'user']);
        $user = $request->user();

        $myVote = null;
        if ($user) {
            $myVote = $poll->votes()->where('user_id', $user->id)->first();
        }

        $resultsVisible = $poll->resultsAreVisibleToUser($user);

        $results = [];
        $totalVotes = 0;

        if ($resultsVisible) {
            $counts = $poll->votes()
                ->select('poll_option_id', DB::raw('COUNT(*) as c'))
                ->groupBy('poll_option_id')
                ->pluck('c', 'poll_option_id');

            $totalVotes = (int) $poll->votes()->count();

            foreach ($poll->options as $opt) {
                $results[$opt->id] = (int) ($counts[$opt->id] ?? 0);
            }
        }

        return view('polls.show', compact('poll', 'myVote', 'resultsVisible', 'results', 'totalVotes'));
    }

    public function create()
    {
        return view('polls.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',

            'is_anonymous' => 'nullable|boolean',
            'anonymous_name' => 'nullable|string|max:60',

            'results_visibility' => 'required|in:immediate,after_end',

            'ends_at' => 'nullable|date', // optional, but needed for after_end to ever reveal

            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:120',
        ]);

        $data['is_anonymous'] = (bool)($data['is_anonymous'] ?? false);
        if ($data['is_anonymous'] && empty($data['anonymous_name'])) {
            $data['anonymous_name'] = 'Anon';
        }

        $poll = Poll::create([
            'user_id' => $request->user()->id,
            'question' => $data['question'],
            'description' => $data['description'] ?? null,
            'is_anonymous' => $data['is_anonymous'],
            'anonymous_name' => $data['anonymous_name'] ?? null,
            'results_visibility' => $data['results_visibility'],
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        $i = 0;
        foreach ($data['options'] as $opt) {
            PollOption::create([
                'poll_id' => $poll->id,
                'label' => trim($opt),
                'sort_order' => $i++,
            ]);
        }

        return redirect()->route('polls.show', $poll);
    }
}
PHP

cat <<'PHP' > app/Http/Controllers/PollVoteController.php
<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollVoteController extends Controller
{
    public function store(Poll $poll, Request $request)
    {
        abort_unless($poll->isOpen(), 403);

        $data = $request->validate([
            'poll_option_id' => 'required|integer',
        ]);

        // option must belong to this poll
        $option = $poll->options()->where('id', $data['poll_option_id'])->firstOrFail();

        // one vote per account (unique index also enforces)
        PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => $request->user()->id],
            ['poll_option_id' => $option->id]
        );

        return redirect()->route('polls.show', $poll)->with('status', 'Vote recorded.');
    }
}
PHP

bold "Creating views..."
mkdir -p resources/views/polls

cat <<'BLADE' > resources/views/polls/index.blade.php
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl">Community Opinion Polls</h2>
  </x-slot>

  <div class="p-6 space-y-4">
    <div class="flex gap-4">
      @auth
        <a class="underline" href="{{ route('polls.create') }}">Suggest a Poll</a>
      @else
        <a class="underline" href="{{ route('login') }}">Login to Suggest/Vote</a>
      @endauth
      <a class="underline" href="{{ route('posts.index') }}">Marketplace</a>
    </div>

    <div class="space-y-3">
      @forelse($polls as $poll)
        <div class="p-4 border rounded">
          <a class="text-lg underline" href="{{ route('polls.show', $poll) }}">{{ $poll->question }}</a>
          <div class="text-sm opacity-70">
            Votes: {{ $poll->votes_count }}
            @if($poll->ends_at) • Ends: {{ $poll->ends_at->format('Y-m-d H:i') }} @endif
            • Results: {{ $poll->results_visibility === 'immediate' ? 'Show immediately' : 'After poll ends' }}
          </div>
        </div>
      @empty
        <div class="p-4 border rounded opacity-70">No polls yet. Be the first to suggest one.</div>
      @endforelse
    </div>

    <div>{{ $polls->links() }}</div>
  </div>
</x-app-layout>
BLADE

cat <<'BLADE' > resources/views/polls/create.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Suggest a Poll</h2></x-slot>

  <div class="p-6 space-y-4">
    <form method="POST" action="{{ route('polls.store') }}" class="space-y-4">
      @csrf

      <div>
        <label>Question</label>
        <input name="question" class="border rounded w-full" maxlength="200" required>
      </div>

      <div>
        <label>Description (optional)</label>
        <textarea name="description" class="border rounded w-full" rows="4" maxlength="5000"></textarea>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_anonymous" value="1">
        <label>Display as anonymous</label>
        <input name="anonymous_name" placeholder="Anon name (optional)" class="border rounded">
      </div>

      <div>
        <label>Results visibility</label>
        <select name="results_visibility" class="border rounded w-full">
          <option value="after_end">Show results only when poll is over</option>
          <option value="immediate">Show results right away</option>
        </select>
      </div>

      <div>
        <label>Ends at (optional but recommended)</label>
        <input type="datetime-local" name="ends_at" class="border rounded">
        <div class="text-sm opacity-70 mt-1">
          If you choose “after poll ends”, results will stay hidden until this time.
        </div>
      </div>

      <div>
        <label>Options (2–10)</label>
        <div class="space-y-2">
          @for($i=0; $i<4; $i++)
            <input name="options[]" class="border rounded w-full" placeholder="Option {{ $i+1 }}" {{ $i < 2 ? 'required' : '' }}>
          @endfor
        </div>
      </div>

      <button class="px-4 py-2 border rounded">Create Poll</button>
    </form>
  </div>
</x-app-layout>
BLADE

cat <<'BLADE' > resources/views/polls/show.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">{{ $poll->question }}</h2></x-slot>

  <div class="p-6 space-y-4">
    <div class="text-sm opacity-70">
      Suggested by:
      @if($poll->is_anonymous) {{ $poll->anonymous_name ?? 'Anon' }}
      @else {{ $poll->user->name }}
      @endif
      • {{ $poll->created_at->diffForHumans() }}
      @if($poll->ends_at) • Ends: {{ $poll->ends_at->format('Y-m-d H:i') }} @endif
    </div>

    @if($poll->description)
      <div class="border rounded p-4 whitespace-pre-wrap">{{ $poll->description }}</div>
    @endif

    @auth
      @if(!$poll->isOpen())
        <div class="p-4 border rounded opacity-70">This poll is closed.</div>
      @endif

      <form method="POST" action="{{ route('polls.vote', $poll) }}" class="space-y-3">
        @csrf

        @foreach($poll->options as $opt)
          <label class="flex items-center gap-2">
            <input type="radio" name="poll_option_id" value="{{ $opt->id }}" {{ ($myVote && $myVote->poll_option_id === $opt->id) ? 'checked' : '' }} required>
            <span>{{ $opt->label }}</span>
          </label>
        @endforeach

        <button class="px-4 py-2 border rounded" {{ !$poll->isOpen() ? 'disabled' : '' }}>
          {{ $myVote ? 'Update Vote' : 'Vote' }}
        </button>
      </form>
    @else
      <div class="border rounded p-4">
        <a class="underline" href="{{ route('login') }}">Login</a> to vote.
      </div>
    @endauth

    <hr>

    @if($resultsVisible)
      <div class="space-y-2">
        <div class="font-semibold">Results (Total votes: {{ $totalVotes }})</div>
        @foreach($poll->options as $opt)
          @php
            $count = $results[$opt->id] ?? 0;
            $pct = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
          @endphp
          <div class="border rounded p-3">
            <div class="flex justify-between">
              <div>{{ $opt->label }}</div>
              <div class="opacity-70">{{ $count }} ({{ $pct }}%)</div>
            </div>
            <div class="mt-2 border rounded h-3 overflow-hidden">
              <div style="width: {{ $pct }}%;" class="h-3 bg-gray-800/30"></div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="p-4 border rounded opacity-70">
        Results are hidden until the poll ends.
      </div>
    @endif

    <div class="pt-2">
      <a class="underline" href="{{ route('polls.index') }}">Back to polls</a> •
      <a class="underline" href="{{ route('posts.index') }}">Marketplace</a>
    </div>
  </div>
</x-app-layout>
BLADE

bold "Wiring routes..."
# Add poll routes if not already present
if ! grep -q "polls.index" routes/web.php; then
  cat <<'ROUTES' >> routes/web.php

use App\Http\Controllers\PollController;
use App\Http\Controllers\PollVoteController;

// Polls (public browse)
Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
Route::get('/polls/create', [PollController::class, 'create'])->middleware(['auth'])->name('polls.create');
Route::post('/polls', [PollController::class, 'store'])->middleware(['auth'])->name('polls.store');
Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');
Route::post('/polls/{poll}/vote', [PollVoteController::class, 'store'])->middleware(['auth'])->name('polls.vote');
ROUTES
fi

bold "Migrating + clearing cache..."
php artisan migrate
php artisan optimize:clear

bold "DONE ✅"
echo "Polls: http://127.0.0.1:8000/polls (or whatever port you're using)"
