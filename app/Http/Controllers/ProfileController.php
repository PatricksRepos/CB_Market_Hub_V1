<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['posts','polls','listings','events']);
        $user->load(['badges' => fn ($q) => $q->orderBy('points_required')]);
        $latestPosts = $user->posts()->latest()->take(5)->get();
        $latestPolls = $user->polls()->latest()->take(5)->get();

        $recentPointActivity = $user->pointTransactions()->latest()->take(10)->get();

        return view('profile.show', compact('user','latestPosts','latestPolls', 'recentPointActivity'));
    }

    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function avatar(User $user): StreamedResponse
    {
        $avatarPath = (string) ($user->getRawOriginal('avatar_url') ?? '');

        abort_unless($avatarPath !== '' && str_starts_with($avatarPath, 'profile-avatars/'), 404);
        abort_unless(Storage::disk('public')->exists($avatarPath), 404);

        return Storage::disk('public')->response($avatarPath);
    }


    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable','string','max:30','regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable','string','max:800'],
            'avatar_image' => ['nullable','image','max:4096'],
        ]);

        if ($data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar_image')) {
            $existingAvatar = (string) ($user->getRawOriginal('avatar_url') ?? '');

            if ($existingAvatar !== '') {
                $existingPath = $existingAvatar;

                if (str_starts_with($existingPath, '/storage/')) {
                    $existingPath = str_replace('/storage/', '', $existingPath);
                } elseif (str_starts_with($existingPath, 'storage/')) {
                    $existingPath = str_replace('storage/', '', $existingPath);
                }

                if (str_starts_with($existingPath, 'profile-avatars/')) {
                    Storage::disk('public')->delete($existingPath);
                }
            }

            $path = $request->file('avatar_image')->store('profile-avatars', 'public');
            $data['avatar_url'] = $path;
        }

        unset($data['avatar_image']);

        $user->fill($data)->save();

        return redirect()->route('profiles.show', $user)->with('status','Profile updated.');
    }
}
