<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'points_required',
        'sort_order',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['earned_at'])
            ->withTimestamps();
    }
}
