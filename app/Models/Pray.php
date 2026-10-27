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
        'orante_nombre',
    ];

    public function getNombreOranteAttribute(): ?string
    {
        if ($this->miembro) {
            return $this->miembro->nombre_completo;
        }

        return $this->orante_nombre;
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'miembro_id');
    }
}
