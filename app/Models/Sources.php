<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sources extends Model
{
    protected $fillable = [
        'inbound_id',
        'url',
        'title',
        'excerpt'
    ];

    public function inbound()
    {
        return $this->belongsTo(Inbounds::class);
    }
}
