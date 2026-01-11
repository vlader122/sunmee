<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\HimnController;
use App\Http\Controllers\MemberController;
use App\Models\Himn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('himnos',[HimnController::class, 'index'] );
Route::get('himnos/{id}',[HimnController::class, 'show'] );
Route::get('miembros', [MemberController::class, 'buscar']);
Route::get('agendas', [AgendaController::class, 'index']);
Route::get('agendas/{id}', [AgendaController::class, 'show']);
Route::post('agendas', [AgendaController::class, 'store']);
Route::put('agendas/{id}', [AgendaController::class, 'update']);
Route::delete('agendas/{id}', [AgendaController::class, 'destroy']);
