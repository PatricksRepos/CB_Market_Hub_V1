<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingInquiry extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_user_id',
        'seller_user_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function messages()
    {
        return $this->hasMany(ListingInquiryMessage::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($subQuery) use ($userId) {
            $subQuery->where('buyer_user_id', $userId)
                ->orWhere('seller_user_id', $userId);
        });
    }

    public function involvesUser(int $userId): bool
    {
        return $this->buyer_user_id === $userId || $this->seller_user_id === $userId;
    }
}
