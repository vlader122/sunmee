<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    protected $fillable = [
        'fecha',
        'tipo_agenda_id',
        'presidido_por_id',
        'dirigido_por_id',
        'pianista_id',
        'director_himno_id',
        'testimonios',
        'anuncios',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function himnos(): HasMany
    {
        return $this->hasMany(HimnAgenda::class, 'agenda_id');
    }

    public function oraciones(): HasMany
    {
        return $this->hasMany(Pray::class, 'agenda_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Message::class, 'agenda_id');
    }

    public function asuntos(): HasMany
    {
        return $this->hasMany(Asunto::class, 'agenda_id');
    }

    public function directorHimno(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'director_himno_id');
    }

    public function presidido(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'presidido_por_id');
    }

    public function dirigido(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'dirigido_por_id');
    }

    public function pianista(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'pianista_id');
    }
}
