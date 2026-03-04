<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['user_id','title','description','location','starts_at','ends_at','is_public'];


    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function rsvps() { return $this->hasMany(EventRsvp::class); }
}
