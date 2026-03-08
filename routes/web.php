<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\CartaController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// PIN de acceso
Route::get('/pin', function () {
    if (session('pin_verified')) {
        return redirect('/dashboard');
    }
    return view('pin');
})->name('pin.show');

Route::post('/pin/verify', function (Request $request) {
    $request->validate(['pin' => 'required|digits:4']);

    if ($request->pin === env('APP_PIN')) {
        session(['pin_verified' => true]);
        return redirect(session('intended', '/dashboard'));
    }

    return back()->withErrors(['pin' => 'Código incorrecto.'])->withInput();
})->name('pin.verify');

Route::get('/pin/logout', function () {
    session()->forget('pin_verified');
    return redirect()->route('pin.show');
})->name('pin.logout');

// Rutas protegidas
Route::middleware('pin')->group(function () {
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


    // Cartas de Asignación
    Route::get('/cartas/generar', [CartaController::class, 'index']);
    Route::post('/api/cartas/asignaciones', [CartaController::class, 'getAsignaciones']);
    Route::post('/api/cartas/preview', [CartaController::class, 'preview']);
    Route::post('/api/cartas/generar-pdf', [CartaController::class, 'generarPDF']);
});

// Públicas (sin PIN)
Route::get('/miembros', function () {
    return view('miembros.index');
});

Route::get('/reunion', [AgendaController::class, 'pdfActual']);
