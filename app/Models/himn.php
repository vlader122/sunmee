<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Himn extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'numero',
        'titulo',
    ];
}
