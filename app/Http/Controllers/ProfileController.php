<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['posts','polls','listings','events']);
        $latestPosts = $user->posts()->latest()->take(5)->get();
        $latestPolls = $user->polls()->latest()->take(5)->get();

        return view('profile.show', compact('user','latestPosts','latestPolls'));
    }

    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable','string','max:30','regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable','string','max:800'],
            'avatar_url' => ['nullable','url','max:255'],
            'avatar_image' => ['nullable','image','max:4096'],
        ]);

        if ($data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar_image')) {
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/profile-avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
            }

            $path = $request->file('avatar_image')->store('profile-avatars', 'public');
            $data['avatar_url'] = Storage::url($path);
        }

        unset($data['avatar_image']);

        $user->fill($data)->save();

        return redirect()->route('profiles.show', $user)->with('status','Profile updated.');
    }
}
