<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbounds extends Model
{
    use HasFactory;
    protected $table = 'inbounds';
    protected $fillable = [
        'url',
        'source',
        'notes',
        'summary',
        'text_path',
        'post_title',
        'user_id'
    ];

    public function metadata()
    {
        return $this->hasOne(PostMetadata::class, 'inbound_id');
    }
}
