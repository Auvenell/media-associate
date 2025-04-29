<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

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
