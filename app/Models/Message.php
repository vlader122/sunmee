<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'orden',
        'tema',
        'tiempo',
        'agenda_id',
        'miembro_id',
        'discursante_id',
        'discursante_nombre', // Para invitados, misioneros, etc.
    ];

    /**
     * Obtener el nombre del discursante (miembro o nombre libre)
     */
    public function getNombreDiscursanteAttribute(): ?string
    {
        if ($this->discursante) {
            return $this->discursante->nombre_completo;
        }

        return $this->discursante_nombre;
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function discursante(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'discursante_id');
    }
}
