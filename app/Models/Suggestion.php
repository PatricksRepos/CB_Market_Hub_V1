<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $fillable = ['user_id','title','body','status','is_anonymous'];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function votes() { return $this->hasMany(SuggestionVote::class); }
    public function reports() { return $this->hasMany(SuggestionReport::class); }
}
