<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['user_id','body','is_deleted'];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function reports() { return $this->hasMany(ChatReport::class); }
}
