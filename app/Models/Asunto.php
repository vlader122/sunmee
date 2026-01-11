<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asunto extends Model
{
    protected $fillable = [
        'orden',
        'tipo_asunto',
        'descripcion',
        'agenda_id',
        'miembro_id',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }
}
