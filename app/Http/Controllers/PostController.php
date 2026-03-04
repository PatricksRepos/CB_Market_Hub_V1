<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query()
            ->where('is_hidden', false)
            ->with(['category.parent', 'user', 'images']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $posts = $query->latest()->paginate(20)->withQueryString();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['category_id'] = $this->resolveCategoryId($data);

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
                PostImage::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'sort_order' => $i++,
                ]);
            }
        }

        return redirect()->route('posts.show', $post);
    }

    public function show(Post $post)
    {
        abort_if($post->is_hidden, 404);

        $post->load(['images', 'user', 'category.parent']);

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $user = request()->user();
        abort_unless(
            $user->id === $post->user_id || $user->isAdmin(),
            403
        );

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless(
            $user->id === $post->user_id || $user->isAdmin(),
            403
        );

        $data = $this->validateData($request);
        $data['category_id'] = $this->resolveCategoryId($data);

        $data['is_anonymous'] = (bool)($data['is_anonymous'] ?? false);

        if ($data['is_anonymous'] && empty($data['anonymous_name'])) {
            $data['anonymous_name'] = 'Anon';
        }

        $post->update($data);

        if ($request->hasFile('images')) {
            $i = (int)($post->images()->max('sort_order') ?? -1) + 1;

            foreach ($request->file('images') as $img) {
                $path = $img->store('post-images', 'public');
                PostImage::create([
                    'post_id' => $post->id,
                    'path' => $path,
                    'sort_order' => $i++,
                ]);
            }
        }

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless(
            $user->id === $post->user_id || $user->isAdmin(),
            403
        );

        $post->load('images');

        foreach ($post->images as $img) {
            Storage::disk('public')->delete($img->path);
        }

        $post->delete();

        return redirect()->route('posts.index');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
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
    }

    private function resolveCategoryId(array $data): ?int
    {
        $categoryId = isset($data['category_id']) ? (int)$data['category_id'] : null;
        $subcategoryId = isset($data['subcategory_id']) ? (int)$data['subcategory_id'] : null;

        if (!$subcategoryId) {
            return $categoryId ?: null;
        }

        $subcategory = Category::query()->whereKey($subcategoryId)->first();
        if (!$subcategory || !$subcategory->parent_id) {
            return $categoryId ?: null;
        }

        if ($categoryId && (int)$subcategory->parent_id !== $categoryId) {
            return $categoryId;
        }

        return $subcategory->id;
    }
}
