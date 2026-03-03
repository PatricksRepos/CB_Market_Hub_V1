<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    protected $fillable = [
        'user_id','category_id','type','title','body',
        'marketplace_action','price','location','condition',
        'is_anonymous','anonymous_name',
        'is_hidden','hidden_at','hidden_reason',
        'status','is_promoted','promoted_until',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_hidden' => 'boolean',
        'hidden_at' => 'datetime',
        'is_promoted' => 'boolean',
        'promoted_until' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function images(): HasMany { return $this->hasMany(PostImage::class)->orderBy('sort_order'); }
    public function reports(): MorphMany { return $this->morphMany(Report::class, 'reportable'); }
}
