<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Notifications\SimpleNotification;
use Illuminate\Support\Str;
use App\Support\Gamification;

class PostCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'body' => ['required','string','min:1','max:2000'],
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        Gamification::award($request->user(), 'post.comment.created', 'post_comment:'.$comment->id);

        // notify post owner (if different)
        if ($post->user_id && (int)$post->user_id !== (int)$request->user()->id) {
            $post->user?->notify(new SimpleNotification(
                'New comment on your post',
                route('posts.show', $post),
                $request->user()->name.' commented: '.Str::limit($comment->body, 80)
            ));
        }

        return back()->with('status','Comment posted.');
    }

    public function destroy(Request $request, Post $post, PostComment $comment)
    {
        if ((int)$comment->post_id !== (int)$post->id) abort(404);
        if ((int)$comment->user_id !== (int)$request->user()->id) abort(403);

        $comment->delete();
        return back()->with('status','Comment deleted.');
    }
}
