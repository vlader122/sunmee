<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pray extends Model
{
    protected $fillable = [
        'tipo',
        'agenda_id',
        'miembro_id',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'miembro_id');
    }
}
