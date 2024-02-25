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
                    'notes',
                    'source'
    ];
}
