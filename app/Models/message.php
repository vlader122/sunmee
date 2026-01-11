<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'orden',
        'tema',
        'agenda_id',
        'miembro_id',
        'discursante_id',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function discursante(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'discursante_id');
    }
}
