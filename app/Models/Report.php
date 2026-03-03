<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_user_id','reportable_type','reportable_id',
        'reason','details','status','handled_by_user_id','handled_at','resolution_notes'
    ];

    protected $casts = ['handled_at' => 'datetime'];

    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_user_id'); }
    public function handledBy(): BelongsTo { return $this->belongsTo(User::class, 'handled_by_user_id'); }
    public function reportable(): MorphTo { return $this->morphTo(); }
}
