<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $agendas = Agenda::with(['directorHimno', 'presidido', 'dirigido', 'pianista', 'himnos', 'oraciones.miembro', 'mensajes.discursante', 'asuntos'])
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return response()->json($agendas);
    }

    public function show($id)
    {
        $agenda = Agenda::with(['directorHimno', 'presidido', 'dirigido', 'pianista', 'himnos', 'oraciones.miembro', 'mensajes.discursante', 'asuntos'])
            ->findOrFail($id);

        return response()->json($agenda);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'tipo_agenda_id' => 'nullable|integer',
            'presidido_por_id' => 'nullable|integer|exists:members,id',
            'dirigido_por_id' => 'nullable|integer|exists:members,id',
            'pianista_id' => 'nullable|integer|exists:members,id',
            'director_himno_id' => 'required|integer|exists:members,id',
            'anuncios' => 'nullable|string',
            'testimonios' => 'nullable|string',

            'himnos' => 'present|array',
            'himnos.*.numero' => 'required|integer',
            'himnos.*.titulo' => 'required|string|max:255',
            'himnos.*.tipo' => 'required|string',

            'oraciones' => 'present|array',
            'oraciones.*.tipo' => 'required|string',
            'oraciones.*.miembro_id' => 'required|integer|exists:members,id',

            'mensajes' => 'present|array',
            'mensajes.*.tema' => 'required|string|max:255',
            'mensajes.*.tiempo' => 'nullable|string|max:50',
            'mensajes.*.discursante_id' => 'nullable|integer|exists:members,id',
            'mensajes.*.discursante_nombre' => 'nullable|string|max:255',

            'asuntos' => 'present|array',
            'asuntos.*.descripcion' => 'required|string',
            'asuntos.*.tipo_asunto' => 'nullable|string',
        ]);

        try { DB::select('SELECT 1'); } catch (\Exception $e) { DB::reconnect(); }

        DB::beginTransaction();

        try {
            $agenda = Agenda::create([
                'fecha' => $validatedData['fecha'],
                'tipo_agenda_id' => $validatedData['tipo_agenda_id'] ?? null,
                'presidido_por_id' => $validatedData['presidido_por_id'] ?? null,
                'dirigido_por_id' => $validatedData['dirigido_por_id'] ?? null,
                'pianista_id' => $validatedData['pianista_id'] ?? null,
                'director_himno_id' => $validatedData['director_himno_id'],
                'anuncios' => $validatedData['anuncios'] ?? null,
                'testimonios' => $validatedData['testimonios'] ?? null,
            ]);

            // 4. Crear los registros "Hijos" usando la relación
            // El método createMany() es perfecto para esto.
            // Automáticamente asignará el 'agenda_sacramental_id'
            
            if (!empty($validatedData['himnos'])) {
                $agenda->himnos()->createMany($validatedData['himnos']);
            }
            
            if (!empty($validatedData['oraciones'])) {
                $agenda->oraciones()->createMany($validatedData['oraciones']);
            }

            if (!empty($validatedData['mensajes'])) {
                $agenda->mensajes()->createMany($validatedData['mensajes']);
            }

            if (!empty($validatedData['asuntos'])) {
                $agenda->asuntos()->createMany($validatedData['asuntos']);
            }

            // 5. Si todo salió bien, confirmar los cambios
            DB::commit();

            // 6. Devolver la agenda creada CON sus relaciones cargadas
            $agenda->load('himnos', 'oraciones', 'mensajes', 'asuntos');

            return response()->json([
                'message' => 'Agenda y detalles creados exitosamente.',
                'data' => $agenda
            ], 201);

        } catch (ValidationException $e) {
            // Si la validación falla (aunque ya la hicimos, es una doble seguridad)
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            // 7. Si algo falló (ej: error de base de datos), deshacer todo
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la agenda.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'tipo_agenda_id' => 'nullable|integer',
            'presidido_por_id' => 'nullable|integer|exists:members,id',
            'dirigido_por_id' => 'nullable|integer|exists:members,id',
            'pianista_id' => 'nullable|integer|exists:members,id',
            'director_himno_id' => 'required|integer|exists:members,id',
            'anuncios' => 'nullable|string',
            'testimonios' => 'nullable|string',

            'himnos' => 'present|array',
            'himnos.*.numero' => 'required|integer',
            'himnos.*.titulo' => 'required|string|max:255',
            'himnos.*.tipo' => 'required|string',

            'oraciones' => 'present|array',
            'oraciones.*.tipo' => 'required|string',
            'oraciones.*.miembro_id' => 'required|integer|exists:members,id',

            'mensajes' => 'present|array',
            'mensajes.*.tema' => 'required|string|max:255',
            'mensajes.*.tiempo' => 'nullable|string|max:50',
            'mensajes.*.discursante_id' => 'nullable|integer|exists:members,id',
            'mensajes.*.discursante_nombre' => 'nullable|string|max:255',

            'asuntos' => 'present|array',
            'asuntos.*.descripcion' => 'required|string',
            'asuntos.*.tipo_asunto' => 'nullable|string',
        ]);

        try { DB::select('SELECT 1'); } catch (\Exception $e) { DB::reconnect(); }

        DB::beginTransaction();

        try {
            \Log::info('Paso 1: Validación completada', ['id' => $id, 'validated' => $validatedData]);
            
            // Buscar agenda sin fail para controlar el error
            $agenda = Agenda::find($id);
            if (!$agenda) {
                \Log::error('Agenda no encontrada', ['id' => $id]);
                throw new \Exception("Agenda con ID {$id} no encontrada");
            }
            \Log::info('Paso 2: Agenda encontrada', ['id' => $id]);

            // Actualizar agenda
            $updateData = [
                'fecha' => $validatedData['fecha'],
                'tipo_agenda_id' => $validatedData['tipo_agenda_id'] ?? null,
                'presidido_por_id' => $validatedData['presidido_por_id'] ?? null,
                'dirigido_por_id' => $validatedData['dirigido_por_id'] ?? null,
                'pianista_id' => $validatedData['pianista_id'] ?? null,
                'director_himno_id' => $validatedData['director_himno_id'],
                'anuncios' => $validatedData['anuncios'] ?? null,
                'testimonios' => $validatedData['testimonios'] ?? null,
            ];
            \Log::info('Paso 3: Intentando update', ['data' => $updateData]);
            
            $agenda->update($updateData);
            \Log::info('Paso 4: Update completado');

            // Eliminar relaciones anteriores
            \Log::info('Paso 5: Eliminando himnos');
            $agenda->himnos()->delete();
            \Log::info('Paso 6: Eliminando oraciones');
            $agenda->oraciones()->delete();
            \Log::info('Paso 7: Eliminando mensajes');
            $agenda->mensajes()->delete();
            \Log::info('Paso 8: Eliminando asuntos');
            $agenda->asuntos()->delete();
            \Log::info('Paso 9: Eliminaciones completadas');

            // Crear nuevas relaciones
            if (!empty($validatedData['himnos'])) {
                \Log::info('Paso 10: Creando himnos', ['count' => count($validatedData['himnos'])]);
                $agenda->himnos()->createMany($validatedData['himnos']);
            }

            if (!empty($validatedData['oraciones'])) {
                \Log::info('Paso 11: Creando oraciones', ['count' => count($validatedData['oraciones']), 'data' => $validatedData['oraciones']]);
                // Verificar que los miembros existan
                foreach ($validatedData['oraciones'] as $index => $oracion) {
                    $exists = \App\Models\Member::where('id', $oracion['miembro_id'])->exists();
                    \Log::info("Verificando miembro oración {$index}", ['miembro_id' => $oracion['miembro_id'], 'exists' => $exists]);
                    if (!$exists) {
                        throw new \Exception("El miembro con ID {$oracion['miembro_id']} no existe para la oración");
                    }
                }
                $agenda->oraciones()->createMany($validatedData['oraciones']);
            }

            if (!empty($validatedData['mensajes'])) {
                \Log::info('Paso 12: Creando mensajes', ['count' => count($validatedData['mensajes']), 'data' => $validatedData['mensajes']]);
                $agenda->mensajes()->createMany($validatedData['mensajes']);
            }

            if (!empty($validatedData['asuntos'])) {
                \Log::info('Paso 13: Creando asuntos', ['count' => count($validatedData['asuntos'])]);
                $agenda->asuntos()->createMany($validatedData['asuntos']);
            }

            DB::commit();
            \Log::info('Paso 14: Commit completado');

            $agenda->load('himnos', 'oraciones', 'mensajes', 'asuntos');

            return response()->json([
                'message' => 'Agenda actualizada exitosamente.',
                'data' => $agenda
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            \Log::error('Error SQL al actualizar agenda:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings()
            ]);
            return response()->json([
                'message' => 'Error de base de datos al actualizar la agenda.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error general al actualizar agenda:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al actualizar la agenda.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $agenda = Agenda::findOrFail($id);

            // Eliminar relaciones
            $agenda->himnos()->delete();
            $agenda->oraciones()->delete();
            $agenda->mensajes()->delete();
            $agenda->asuntos()->delete();

            $agenda->delete();

            DB::commit();

            return response()->json([
                'message' => 'Agenda eliminada exitosamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al eliminar la agenda.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function himnosRecientes(Request $request)
    {
        // Obtener las últimas 4 semanas por defecto
        $semanas = $request->input('semanas', 4);
        $fechaDesde = now()->subWeeks($semanas)->startOfDay();

        $himnos = Agenda::with(['himnos'])
            ->where('fecha', '>=', $fechaDesde)
            ->orderBy('fecha', 'desc')
            ->get()
            ->flatMap(function ($agenda) {
                return $agenda->himnos->map(function ($himno) use ($agenda) {
                    return [
                        'numero' => $himno->numero,
                        'titulo' => $himno->titulo,
                        'tipo' => $himno->tipo,
                        'fecha' => $agenda->fecha,
                        'fecha_formateada' => $agenda->fecha->format('d/m/Y'),
                    ];
                });
            })
            ->groupBy('numero')
            ->map(function ($grupo) {
                return [
                    'numero' => $grupo->first()['numero'],
                    'titulo' => $grupo->first()['titulo'],
                    'usos' => $grupo->count(),
                    'ultimo_uso' => $grupo->first()['fecha_formateada'],
                    'fechas' => $grupo->pluck('fecha_formateada')->toArray(),
                ];
            })
            ->values();

        return response()->json($himnos);
    }

    public function pdf($id)
    {
        $agenda = Agenda::with(['directorHimno', 'presidido', 'dirigido', 'pianista', 'himnos', 'oraciones.miembro', 'mensajes.discursante', 'asuntos'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('agenda.pdf', compact('agenda'));
        $pdf->setPaper('letter', 'portrait');

        $fecha = date('Y-m-d', strtotime($agenda->fecha));
        return $pdf->stream("agenda-sacramental-{$fecha}.pdf");
    }

    public function pdfActual()
    {
        // Busca la agenda del domingo más próximo (esta semana o la siguiente)
        $hoy = now()->startOfDay();
        $diasHastaDomingo = (7 - $hoy->dayOfWeek) % 7;
        $domingo = $hoy->copy()->addDays($diasHastaDomingo ?: 7);

        // Busca en un rango de ±3 días alrededor del domingo de esta semana
        $agenda = Agenda::with(['directorHimno', 'presidido', 'dirigido', 'pianista', 'himnos', 'oraciones.miembro', 'mensajes.discursante', 'asuntos'])
            ->whereBetween('fecha', [$domingo->copy()->subDays(3), $domingo->copy()->addDays(3)])
            ->orderBy('fecha', 'asc')
            ->first();

        if (!$agenda) {
            abort(404, 'No hay agenda disponible para esta semana.');
        }

        $pdf = Pdf::loadView('agenda.pdf', compact('agenda'));
        $pdf->setPaper('letter', 'portrait');

        $fecha = date('Y-m-d', strtotime($agenda->fecha));
        return $pdf->stream("agenda-sacramental-{$fecha}.pdf");
    }
}
