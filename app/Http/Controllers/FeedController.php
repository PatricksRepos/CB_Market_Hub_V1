<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Poll;
use App\Models\PostComment;
use App\Models\PollComment;

class FeedController extends Controller
{
    public function index()
    {
        $posts = Post::query()->latest()->take(10)->get();
        $polls = Poll::query()->latest()->take(10)->get();
        $postComments = PostComment::query()->latest()->with(['user','post'])->take(10)->get();
        $pollComments = PollComment::query()->latest()->with(['user','poll'])->take(10)->get();

        $items = collect()
            ->concat($posts->map(fn($p)=>['type'=>'post','at'=>$p->created_at,'data'=>$p]))
            ->concat($polls->map(fn($p)=>['type'=>'poll','at'=>$p->created_at,'data'=>$p]))
            ->concat($postComments->map(fn($c)=>['type'=>'post_comment','at'=>$c->created_at,'data'=>$c]))
            ->concat($pollComments->map(fn($c)=>['type'=>'poll_comment','at'=>$c->created_at,'data'=>$c]))
            ->sortByDesc('at')
            ->take(25)
            ->values();

        return view('feed.index', compact('items'));
    }
}
