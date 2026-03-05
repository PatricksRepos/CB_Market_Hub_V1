<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingInquiryMessage extends Model
{
    protected $fillable = [
        'listing_inquiry_id',
        'sender_user_id',
        'body',
    ];

    public function inquiry()
    {
        return $this->belongsTo(ListingInquiry::class, 'listing_inquiry_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
