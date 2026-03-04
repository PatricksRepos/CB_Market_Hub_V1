<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['posts','polls','listings','events']);
        $latestPosts = $user->posts()->latest()->take(5)->get();
        $latestPolls = $user->polls()->latest()->take(5)->get();
        return view('profiles.show', compact('user','latestPosts','latestPolls'));
    }

    public function edit(Request $request)
    {
        return view('profiles.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['nullable','string','max:30','regex:/^[a-zA-Z0-9_]+$/','unique:users,username,'.$user->id],
            'bio' => ['nullable','string','max:800'],
            'avatar_url' => ['nullable','url','max:255'],
        ]);

        $user->update($data);
        return redirect()->route('profiles.show', $user)->with('status','Profile updated.');
    }
}
