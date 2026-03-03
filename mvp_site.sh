#!/usr/bin/env bash
set -euo pipefail

bold(){ printf "\033[1m%s\033[0m\n" "$*"; }

bold "Finding migration files..."
POST_IMAGES_MIG="$(ls -1 database/migrations/*create_post_images_table.php | tail -n 1)"
REPORTS_MIG="$(ls -1 database/migrations/*create_reports_table.php 2>/dev/null | tail -n 1 || true)"

if [[ -z "${POST_IMAGES_MIG:-}" ]]; then
  echo "ERROR: create_post_images_table migration not found."; exit 1
fi

if [[ -z "${REPORTS_MIG:-}" ]]; then
  # create it if missing
  php artisan make:model Report -m >/dev/null
  REPORTS_MIG="$(ls -1 database/migrations/*create_reports_table.php | tail -n 1)"
fi

bold "Writing post_images migration: $POST_IMAGES_MIG"
cat <<'PHP' > "$POST_IMAGES_MIG"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
PHP

bold "Writing reports migration: $REPORTS_MIG"
cat <<'PHP' > "$REPORTS_MIG"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('reportable_type'); // App\Models\Post, App\Models\Poll
            $table->unsignedBigInteger('reportable_id');

            $table->string('reason'); // spam, scam, hate, harassment, illegal, other
            $table->text('details')->nullable();

            $table->string('status')->default('open')->index(); // open, reviewing, resolved, rejected
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
PHP

bold "Writing model relationships..."
mkdir -p app/Models

cat <<'PHP' > app/Models/Category.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = ['parent_id','name','slug','sort_order','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function parent(): BelongsTo { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order'); }
    public function posts(): HasMany { return $this->hasMany(Post::class); }
}
PHP

cat <<'PHP' > app/Models/Post.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    protected $fillable = [
        'user_id','category_id','type','title','body',
        'marketplace_action','price','location','condition',
        'is_anonymous','anonymous_name',
        'is_hidden','hidden_at','hidden_reason',
        'status','is_promoted','promoted_until',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_hidden' => 'boolean',
        'hidden_at' => 'datetime',
        'is_promoted' => 'boolean',
        'promoted_until' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(PostImage::class)->orderBy('sort_order'); }
    public function reports(): MorphMany { return $this->morphMany(Report::class, 'reportable'); }
}
PHP

cat <<'PHP' > app/Models/PostImage.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    protected $fillable = ['post_id','path','sort_order'];
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
PHP

cat <<'PHP' > app/Models/Report.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_user_id','reportable_type','reportable_id',
        'reason','details','status','handled_by_user_id','handled_at','resolution_notes'
    ];

    protected $casts = ['handled_at' => 'datetime'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_user_id'); }
    public function handledBy(): BelongsTo { return $this->belongsTo(User::class, 'handled_by_user_id'); }
    public function reportable(): MorphTo { return $this->morphTo(); }
}
PHP

bold "Creating controllers..."
php artisan make:controller PostController --resource >/dev/null || true
php artisan make:controller ReportController >/dev/null || true

cat <<'PHP' > app/Http/Controllers/PostController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function index()
    {
        $posts = Post::query()
            ->where('is_hidden', false)
            ->latest()
            ->paginate(20);

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:marketplace,business,discussion',
            'title' => 'required|string|max:180',
            'body' => 'required|string',
            'marketplace_action' => 'nullable|in:buy,sell,trade',
            'price' => 'nullable|numeric|min:0|max:9999999.99',
            'location' => 'nullable|string|max:120',
            'condition' => 'nullable|string|max:60',
            'is_anonymous' => 'nullable|boolean',
            'anonymous_name' => 'nullable|string|max:60',
            'images.*' => 'nullable|image|max:8192',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['is_anonymous'] = (bool)($data['is_anonymous'] ?? false);
        if ($data['is_anonymous'] && empty($data['anonymous_name'])) {
            $data['anonymous_name'] = 'Anon';
        }

        $post = Post::create($data);

        if ($request->hasFile('images')) {
            $i = 0;
            foreach ($request->file('images') as $img) {
                $path = $img->store('post-images', 'public');
                PostImage::create(['post_id' => $post->id, 'path' => $path, 'sort_order' => $i++]);
            }
        }

        return redirect()->route('posts.show', $post);
    }

    public function show(Post $post)
    {
        abort_if($post->is_hidden, 404);
        $post->load(['images','user']);
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $user = request()->user();
        abort_unless($user->id === $post->user_id || $user->hasRole('admin') || $user->hasRole('moderator'), 403);
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless($user->id === $post->user_id || $user->hasRole('admin') || $user->hasRole('moderator'), 403);

        $data = $request->validate([
            'type' => 'required|in:marketplace,business,discussion',
            'title' => 'required|string|max:180',
            'body' => 'required|string',
            'marketplace_action' => 'nullable|in:buy,sell,trade',
            'price' => 'nullable|numeric|min:0|max:9999999.99',
            'location' => 'nullable|string|max:120',
            'condition' => 'nullable|string|max:60',
            'is_anonymous' => 'nullable|boolean',
            'anonymous_name' => 'nullable|string|max:60',
            'images.*' => 'nullable|image|max:8192',
        ]);

        $data['is_anonymous'] = (bool)($data['is_anonymous'] ?? false);
        if ($data['is_anonymous'] && empty($data['anonymous_name'])) {
            $data['anonymous_name'] = 'Anon';
        }

        $post->update($data);

        if ($request->hasFile('images')) {
            $i = (int)($post->images()->max('sort_order') ?? -1) + 1;
            foreach ($request->file('images') as $img) {
                $path = $img->store('post-images', 'public');
                PostImage::create(['post_id' => $post->id, 'path' => $path, 'sort_order' => $i++]);
            }
        }

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless($user->id === $post->user_id || $user->hasRole('admin') || $user->hasRole('moderator'), 403);

        $post->load('images');
        foreach ($post->images as $img) {
            Storage::disk('public')->delete($img->path);
        }
        $post->delete();

        return redirect()->route('posts.index');
    }
}
PHP

cat <<'PHP' > app/Http/Controllers/ReportController.php
<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reportable_type' => 'required|in:post',
            'reportable_id' => 'required|integer|min:1',
            'reason' => 'required|in:spam,scam,hate,harassment,illegal,other',
            'details' => 'nullable|string|max:2000',
        ]);

        $typeMap = [
            'post' => \App\Models\Post::class,
        ];

        Report::create([
            'reporter_user_id' => $request->user()->id,
            'reportable_type' => $typeMap[$data['reportable_type']],
            'reportable_id' => $data['reportable_id'],
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('status', 'Report submitted. Thanks.');
    }
}
PHP

bold "Wiring routes..."
if ! grep -q "Route::resource('posts'" routes/web.php; then
  cat <<'ROUTES' >> routes/web.php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth','verified'])->group(function () {
    Route::resource('posts', PostController::class);
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});
ROUTES
fi

bold "Creating basic views..."
mkdir -p resources/views/posts

cat <<'BLADE' > resources/views/posts/index.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Marketplace & Posts</h2></x-slot>
  <div class="p-6">
    <a class="underline" href="{{ route('posts.create') }}">Create Post</a>
    <div class="mt-4 space-y-3">
      @foreach($posts as $post)
        <div class="p-4 border rounded">
          <div class="text-sm opacity-70">{{ $post->type }} @if($post->is_promoted) • PROMOTED @endif</div>
          <a class="text-lg underline" href="{{ route('posts.show',$post) }}">{{ $post->title }}</a>
          <div class="text-sm opacity-70">{{ $post->location }} @if($post->price) • ${{ $post->price }} @endif</div>
        </div>
      @endforeach
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
  </div>
</x-app-layout>
BLADE

cat <<'BLADE' > resources/views/posts/create.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Create Post</h2></x-slot>
  <div class="p-6">
    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label>Type</label>
        <select name="type" class="border rounded w-full">
          <option value="marketplace">Marketplace</option>
          <option value="business">Business / Service</option>
          <option value="discussion">Discussion</option>
        </select>
      </div>
      <div><label>Title</label><input name="title" class="border rounded w-full" required></div>
      <div><label>Body</label><textarea name="body" class="border rounded w-full" rows="6" required></textarea></div>

      <div class="grid grid-cols-2 gap-4">
        <div><label>Action</label><select name="marketplace_action" class="border rounded w-full">
          <option value="">(none)</option><option value="buy">Buy</option><option value="sell">Sell</option><option value="trade">Trade</option>
        </select></div>
        <div><label>Price</label><input name="price" type="number" step="0.01" class="border rounded w-full"></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label>Location</label><input name="location" class="border rounded w-full"></div>
        <div><label>Condition</label><input name="condition" class="border rounded w-full"></div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_anonymous" value="1">
        <label>Display as anonymous</label>
        <input name="anonymous_name" placeholder="Anon name (optional)" class="border rounded">
      </div>

      <div><label>Images</label><input type="file" name="images[]" multiple accept="image/*"></div>

      <button class="px-4 py-2 border rounded">Post</button>
    </form>
  </div>
</x-app-layout>
BLADE

cat <<'BLADE' > resources/views/posts/show.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">{{ $post->title }}</h2></x-slot>
  <div class="p-6 space-y-4">
    <div class="text-sm opacity-70">
      Posted by:
      @if($post->is_anonymous) {{ $post->anonymous_name ?? 'Anon' }}
      @else {{ $post->user->name }}
      @endif
      • {{ $post->created_at->diffForHumans() }}
    </div>

    <div class="border rounded p-4 whitespace-pre-wrap">{{ $post->body }}</div>

    @if($post->images->count())
      <div class="grid grid-cols-3 gap-3">
        @foreach($post->images as $img)
          <img class="rounded border" src="{{ asset('storage/'.$img->path) }}" alt="image">
        @endforeach
      </div>
    @endif

    <div class="flex gap-3">
      <a class="underline" href="{{ route('posts.edit',$post) }}">Edit</a>
      <form method="POST" action="{{ route('posts.destroy',$post) }}">
        @csrf @method('DELETE')
        <button class="underline" onclick="return confirm('Hard delete this post?')">Delete</button>
      </form>
    </div>

    <hr>

    <form method="POST" action="{{ route('reports.store') }}" class="space-y-2">
      @csrf
      <input type="hidden" name="reportable_type" value="post">
      <input type="hidden" name="reportable_id" value="{{ $post->id }}">
      <label>Report this post</label>
      <select name="reason" class="border rounded">
        <option value="spam">Spam</option>
        <option value="scam">Scam</option>
        <option value="hate">Hate</option>
        <option value="harassment">Harassment</option>
        <option value="illegal">Illegal</option>
        <option value="other">Other</option>
      </select>
      <input name="details" class="border rounded w-full" placeholder="Optional details">
      <button class="px-3 py-1 border rounded">Submit report</button>
    </form>
  </div>
</x-app-layout>
BLADE

cat <<'BLADE' > resources/views/posts/edit.blade.php
<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Edit Post</h2></x-slot>
  <div class="p-6">
    <form method="POST" action="{{ route('posts.update',$post) }}" enctype="multipart/form-data" class="space-y-4">
      @csrf @method('PUT')
      <div>
        <label>Type</label>
        <select name="type" class="border rounded w-full">
          <option value="marketplace" @selected($post->type==='marketplace')>Marketplace</option>
          <option value="business" @selected($post->type==='business')>Business / Service</option>
          <option value="discussion" @selected($post->type==='discussion')>Discussion</option>
        </select>
      </div>
      <div><label>Title</label><input name="title" class="border rounded w-full" value="{{ $post->title }}" required></div>
      <div><label>Body</label><textarea name="body" class="border rounded w-full" rows="6" required>{{ $post->body }}</textarea></div>
      <div><label>Add images</label><input type="file" name="images[]" multiple accept="image/*"></div>
      <button class="px-4 py-2 border rounded">Save</button>
    </form>
  </div>
</x-app-layout>
BLADE

bold "Running migrations + storage link..."
php artisan migrate
php artisan storage:link || true
php artisan optimize:clear

bold "DONE ✅"
echo "Go to: http://127.0.0.1:8000/posts (requires login + verified)"
