<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollComment extends Model
{
    protected $fillable = ['poll_id', 'user_id', 'body'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
