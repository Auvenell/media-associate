<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMetadata extends Model
{
    protected $table = 'post_metadata';

    protected $fillable = [
        'inbound_id',
        'categories',
        'sentiment',
        'market_mover',
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    public function inbound()
    {
        return $this->belongsTo(Inbounds::class, 'inbound_id');
    }
}
