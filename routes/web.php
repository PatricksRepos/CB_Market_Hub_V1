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
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ModerationController;

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
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profiles.update');
    Route::post('/profile', [ProfileController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markOneRead'])->name('notifications.read-one');
    Route::post('/chat/{message}/report', [\App\Http\Controllers\ChatController::class, 'report'])->middleware('throttle:chat-report')->name('chat.report');
});

/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/create', [PostController::class, 'create'])->middleware(['auth'])->name('posts.create');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])->middleware('throttle:comments')->name('posts.comments.store');
    Route::delete('/posts/{post}/comments/{comment}', [PostCommentController::class, 'destroy'])->middleware('throttle:comments')->name('posts.comments.destroy');

    Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:reports')->name('reports.store');

    Route::post('/reactions', [ReactionController::class, 'store'])->middleware('throttle:reactions')->name('reactions.store');
});

/*
|--------------------------------------------------------------------------
| Polls (your existing system)
|--------------------------------------------------------------------------
*/
Route::get('/polls', [PollController::class, 'index'])->name('polls.index');
Route::get('/polls/create', [PollController::class, 'create'])->middleware(['auth'])->name('polls.create');
Route::get('/polls/{poll}', [PollController::class, 'show'])->name('polls.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/polls', [PollController::class, 'store'])->name('polls.store');

    Route::post('/polls/{poll}/vote', [PollController::class, 'vote'])->middleware('throttle:votes')->name('polls.vote');
    Route::post('/polls/{poll}/end', [PollController::class, 'endEarly'])->name('polls.end');
    Route::patch('/polls/{poll}/visibility', [PollController::class, 'updateVisibility'])->name('polls.visibility');
    Route::delete('/polls/{poll}', [PollController::class, 'destroy'])->name('polls.destroy');

    Route::post('/polls/{poll}/comments', [PollCommentController::class, 'store'])->middleware('throttle:comments')->name('polls.comments.store');
    Route::delete('/polls/{poll}/comments/{comment}', [PollCommentController::class, 'destroy'])->middleware('throttle:comments')->name('polls.comments.destroy');
});

/*
|--------------------------------------------------------------------------
| Marketplace
|--------------------------------------------------------------------------
*/
Route::get('/marketplace', [ListingController::class, 'index'])->name('listings.index');
Route::get('/marketplace/create', [ListingController::class, 'create'])->middleware(['auth'])->name('listings.create');
Route::get('/marketplace/{listing}', [ListingController::class, 'show'])->name('listings.show');

Route::middleware(['auth'])->group(function () {
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
Route::get('/events/create', [EventController::class, 'create'])->middleware(['auth'])->name('events.create');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::match(['put', 'patch'], '/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
});
/*
|--------------------------------------------------------------------------
| Suggestions
|--------------------------------------------------------------------------
*/

Route::get('/suggestions', [\App\Http\Controllers\SuggestionController::class, 'index'])->name('suggestions.index');
Route::get('/suggestions/create', [\App\Http\Controllers\SuggestionController::class, 'create'])->middleware(['auth'])->name('suggestions.create');
Route::post('/suggestions', [\App\Http\Controllers\SuggestionController::class, 'store'])->middleware(['auth'])->name('suggestions.store');
Route::get('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'show'])->name('suggestions.show');
Route::post('/suggestions/{suggestion}/vote', [\App\Http\Controllers\SuggestionController::class, 'vote'])->middleware(['auth', 'throttle:votes'])->name('suggestions.vote');
Route::delete('/suggestions/{suggestion}/vote', [\App\Http\Controllers\SuggestionController::class, 'unvote'])->middleware(['auth', 'throttle:votes'])->name('suggestions.unvote');
Route::post('/suggestions/{suggestion}/report', [\App\Http\Controllers\SuggestionController::class, 'report'])->middleware(['auth', 'throttle:reports'])->name('suggestions.report');
Route::patch('/suggestions/{suggestion}/status', [\App\Http\Controllers\SuggestionController::class, 'setStatus'])->middleware(['auth'])->name('suggestions.status');
Route::get('/suggestions/{suggestion}/edit', [\App\Http\Controllers\SuggestionController::class, 'edit'])->middleware(['auth'])->name('suggestions.edit');
Route::put('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'update'])->middleware(['auth'])->name('suggestions.update');
Route::delete('/suggestions/{suggestion}', [\App\Http\Controllers\SuggestionController::class, 'destroy'])->middleware(['auth'])->name('suggestions.destroy');


/*
|--------------------------------------------------------------------------
| Moderation
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::patch('/moderation/reports/{report}', [ModerationController::class, 'updateStatus'])->name('moderation.reports.update');
});

/*
|--------------------------------------------------------------------------
| Chat
|--------------------------------------------------------------------------
*/

Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/fetch', [\App\Http\Controllers\ChatController::class, 'fetch'])->middleware('throttle:chat-fetch')->name('chat.fetch');
Route::post('/chat', [\App\Http\Controllers\ChatController::class, 'send'])->middleware(['auth', 'throttle:chat-send'])->name('chat.send');
Route::delete('/chat/{message}', [\App\Http\Controllers\ChatController::class, 'delete'])->middleware(['auth', 'throttle:chat-send'])->name('chat.delete');
require __DIR__.'/auth.php';
