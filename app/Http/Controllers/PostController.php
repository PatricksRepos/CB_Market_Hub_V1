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
