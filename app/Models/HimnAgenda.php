<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HimnAgenda extends Model
{
    protected $table = 'himn_agendas';

    protected $fillable = [
        'numero',
        'titulo',
        'tipo',
        'himno_id',
        'agenda_id',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function himno(): BelongsTo
    {
        return $this->belongsTo(Himn::class, 'himno_id');
    }
}
