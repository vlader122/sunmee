<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nombre_completo',
        'telefono',
        'edad',
        'telefono_updated_at',
    ];

    protected $casts = [
        'telefono_updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Member $member) {
            if ($member->isDirty('telefono') && !empty($member->telefono)) {
                $member->telefono_updated_at = now();
            }
        });
    }
}
