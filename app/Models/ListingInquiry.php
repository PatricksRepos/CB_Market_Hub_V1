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
        'buyer_last_read_at',
        'seller_last_read_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'buyer_last_read_at' => 'datetime',
        'seller_last_read_at' => 'datetime',
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

    public function unreadMessagesCountFor(int $userId): int
    {
        $lastReadAt = (int) $this->buyer_user_id === $userId
            ? $this->buyer_last_read_at
            : $this->seller_last_read_at;

        return $this->messages()
            ->where('sender_user_id', '!=', $userId)
            ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
            ->count();
    }
}
