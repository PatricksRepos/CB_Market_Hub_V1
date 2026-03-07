<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'username',
        'bio',
        'is_admin',
        'points_total',
        'current_badge_slug',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'points_total' => 'integer',
    ];

    public function isAdmin(): bool
    {
        return (bool)($this->is_admin ?? false);
    }

    // Existing app relationships (safe if the models exist)
    public function posts() { return $this->hasMany(\App\Models\Post::class); }
    public function polls() { return $this->hasMany(\App\Models\Poll::class); }
    public function reports() { return $this->hasMany(\App\Models\Report::class); }
    public function listings() { return $this->hasMany(\App\Models\Listing::class); }
    public function events() { return $this->hasMany(\App\Models\Event::class); }
    public function eventRsvps() { return $this->hasMany(\App\Models\EventRsvp::class); }

    // New hub features
    public function suggestions() { return $this->hasMany(\App\Models\Suggestion::class); }
    public function suggestionVotes() { return $this->hasMany(\App\Models\SuggestionVote::class); }
    public function chatMessages() { return $this->hasMany(\App\Models\ChatMessage::class); }
    public function listingInquiriesAsBuyer() { return $this->hasMany(\App\Models\ListingInquiry::class, 'buyer_user_id'); }
    public function listingInquiriesAsSeller() { return $this->hasMany(\App\Models\ListingInquiry::class, 'seller_user_id'); }
    public function listingInquiryMessages() { return $this->hasMany(\App\Models\ListingInquiryMessage::class, 'sender_user_id'); }
    public function pointTransactions() { return $this->hasMany(\App\Models\PointTransaction::class); }
    public function badges() { return $this->belongsToMany(\App\Models\Badge::class)->withPivot(['earned_at'])->withTimestamps(); }

}

