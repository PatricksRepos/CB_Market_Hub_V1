<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuggestionReport extends Model
{
    protected $fillable = ['suggestion_id','user_id','reason'];

    public function suggestion() { return $this->belongsTo(Suggestion::class); }
    public function user() { return $this->belongsTo(User::class); }
}
