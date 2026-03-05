<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Listing extends Model
{
    protected $fillable = [
        'user_id','title','body','price_cents','location','category','is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function inquiries()
    {
        return $this->hasMany(ListingInquiry::class);
    }

}
