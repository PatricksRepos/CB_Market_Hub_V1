<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PollController;

Route::get('/', function () {
    return redirect()->route('posts.index');
});

// Needed by auth scaffolding
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return redirect()->route('posts.index');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/

// Public list + show
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// Auth-only create/manage MUST come before /posts/{post}
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

// show route AFTER create
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

/*
|--------------------------------------------------------------------------
| Polls
|--------------------------------------------------------------------------
*/

// Public index
Route::get('/polls', [PollController::class, 'index'])->name('polls.index');

// Auth-only create/vote MUST come before /polls/{poll}
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/polls/create', [PollController::class, 'create'])->name('polls.create');
    Route::post('/polls', [PollController::class, 'store'])->name('polls.store');
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
});

// show route AFTER /polls/create
Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');

require __DIR__.'/auth.php';
