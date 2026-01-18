<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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

// WhatsApp Configuration
Route::get('/whatsapp/config', [WhatsAppController::class, 'config']);

// Miembros
Route::get('/miembros', function () {
    return view('miembros.index');
});
