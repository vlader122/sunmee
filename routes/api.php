<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\HimnController;
use App\Http\Controllers\ImportarMiembrosController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('himnos', [HimnController::class, 'index']);
Route::get('himnos/{id}', [HimnController::class, 'show']);
Route::get('miembros', [MemberController::class, 'buscar']);
Route::get('miembros/list', [MemberController::class, 'list']);
Route::get('miembros/sugerencias', [MemberController::class, 'sugerencias']);
Route::get('miembros/stats', [MemberController::class, 'stats']);
Route::get('miembros/telefonos-actualizados', [MemberController::class, 'telefonosActualizados']);
Route::post('miembros', [MemberController::class, 'store']);
Route::post('miembros/importar', [MemberController::class, 'importar']);
Route::post('miembros/importar-pdf', [ImportarMiembrosController::class, 'parsear']);
Route::post('miembros/importar-pdf/confirmar', [ImportarMiembrosController::class, 'importar']);
Route::put('miembros/{id}', [MemberController::class, 'update']);
Route::delete('miembros/{id}', [MemberController::class, 'destroy']);
Route::get('agendas', [AgendaController::class, 'index']);
Route::get('agendas/{id}', [AgendaController::class, 'show']);
Route::post('agendas', [AgendaController::class, 'store']);
Route::put('agendas/{id}', [AgendaController::class, 'update']);
Route::delete('agendas/{id}', [AgendaController::class, 'destroy']);
Route::get('agendas/himnos/recientes', [AgendaController::class, 'himnosRecientes']);

// WhatsApp API
Route::get('whatsapp/status', [WhatsAppController::class, 'status']);
Route::get('whatsapp/test', [WhatsAppController::class, 'testConnection']);
Route::post('whatsapp/config', [WhatsAppController::class, 'saveConfig']);
Route::post('whatsapp/send/{agenda}', [WhatsAppController::class, 'sendNotifications']);
