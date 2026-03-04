<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\PostController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\ReportController;

use App\Http\Controllers\PollController;
use App\Http\Controllers\PollCommentController;

use App\Http\Controllers\ListingController;
use App\Http\Controllers\EventController;

Route::get('/', [FeedController::class, 'index'])->name('feed.index');

Route::middleware(['auth','verified'])->get('/dashboard', function () {
    return redirect()->route('feed.index');
})->name('dashboard');
/*
|--------------------------------------------------------------------------
| Profiles
|--------------------------------------------------------------------------
*/
Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profiles.show');
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profiles.update');
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/chat/{message}/report', [\App\Http\Controllers\ChatController::class, 'report'])->name('chat.report');
});

/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/posts/{post}/comments/{comment}', [PostCommentController::class, 'destroy'])->name('posts.comments.destroy');

    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});

/*
|--------------------------------------------------------------------------
| Polls (your existing system)
|--------------------------------------------------------------------------
*/
Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/polls/create', [PollController::class, 'create'])->name('polls.create');
    Route::post('/polls', [PollController::class, 'store'])->middleware('throttle:5,60')->name('polls.store');

    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
    Route::post('/polls/{poll}/end', [PollController::class, 'endEarly'])->name('polls.end');
    Route::patch('/polls/{poll}/visibility', [PollController::class, 'updateVisibility'])->name('polls.visibility');
    Route::delete('/polls/{poll}', [PollController::class, 'destroy'])->name('polls.destroy');

    Route::post('/polls/{poll}/comments', [PollCommentController::class, 'store'])->name('polls.comments.store');
    Route::delete('/polls/{poll}/comments/{comment}', [PollCommentController::class, 'destroy'])->name('polls.comments.destroy');
});

/*
|--------------------------------------------------------------------------
| Marketplace
|--------------------------------------------------------------------------
*/
Route::get('/marketplace', [ListingController::class, 'index'])->name('listings.index');
Route::get('/marketplace/{listing}', [ListingController::class, 'show'])->name('listings.show');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/marketplace/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/marketplace', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/marketplace/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');
    Route::put('/marketplace/{listing}', [ListingController::class, 'update'])->name('listings.update');
    Route::delete('/marketplace/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
});

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
});
/*
|--------------------------------------------------------------------------
| Suggestions
|--------------------------------------------------------------------------
*/

Route::get('/suggestions', [\App\Http\Controllers\SuggestionController::class, 'index'])->name('suggestions.index');
Route::get('/suggestions/create', [\App\Http\Controllers\SuggestionController::class, 'create'])->middleware(['auth','verified'])->name('suggestions.create');
Route::post('/suggestions', [\App\Http\Controllers\SuggestionController::class, 'store'])->middleware(['auth','verified'])->name('suggestions.store');
Route::get('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'show'])->name('suggestions.show');
Route::post('/suggestions/{suggestion}/vote', [\App\Http\Controllers\SuggestionController::class, 'vote'])->middleware(['auth','verified'])->name('suggestions.vote');
Route::delete('/suggestions/{suggestion}/vote', [\App\Http\Controllers\SuggestionController::class, 'unvote'])->middleware(['auth','verified'])->name('suggestions.unvote');
Route::post('/suggestions/{suggestion}/report', [\App\Http\Controllers\SuggestionController::class, 'report'])->middleware(['auth','verified'])->name('suggestions.report');
Route::patch('/suggestions/{suggestion}/status', [\App\Http\Controllers\SuggestionController::class, 'setStatus'])->middleware(['auth','verified'])->name('suggestions.status');
Route::get('/suggestions/{suggestion}/edit', [\App\Http\Controllers\SuggestionController::class, 'edit'])->middleware(['auth','verified'])->name('suggestions.edit');
Route::put('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'update'])->middleware(['auth','verified'])->name('suggestions.update');
Route::delete('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'destroy'])->middleware(['auth','verified'])->name('suggestions.destroy');


/*
|--------------------------------------------------------------------------
| Chat
|--------------------------------------------------------------------------
*/

Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/fetch', [\App\Http\Controllers\ChatController::class, 'fetch'])->name('chat.fetch');
Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'send'])->middleware(['auth','verified'])->name('chat.send');
Route::delete('/chat/{message}', [\App\Http\Controllers\ChatController::class, 'delete'])->middleware(['auth','verified'])->name('chat.delete');
require __DIR__.'/auth.php';
