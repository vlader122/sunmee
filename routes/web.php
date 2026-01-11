<?php

use App\Http\Controllers\AgendaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/agenda/crear', function () {
    return view('agenda.crear');
});

Route::get('/agenda/historial', function () {
    return view('agenda.historial');
});

Route::get('/agenda/editar/{id}', function ($id) {
    return view('agenda.editar', ['agendaId' => $id]);
});

Route::get('/agenda/pdf/{id}', [AgendaController::class, 'pdf']);
