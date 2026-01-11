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
            'mensajes.*.discursante_id' => 'required|integer|exists:members,id',

            'asuntos' => 'present|array',
            'asuntos.*.descripcion' => 'required|string',
            'asuntos.*.tipo_asunto' => 'nullable|string',
        ]);

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
            'mensajes.*.discursante_id' => 'required|integer|exists:members,id',

            'asuntos' => 'present|array',
            'asuntos.*.descripcion' => 'required|string',
            'asuntos.*.tipo_asunto' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $agenda = Agenda::findOrFail($id);

            $agenda->update([
                'fecha' => $validatedData['fecha'],
                'tipo_agenda_id' => $validatedData['tipo_agenda_id'] ?? null,
                'presidido_por_id' => $validatedData['presidido_por_id'] ?? null,
                'dirigido_por_id' => $validatedData['dirigido_por_id'] ?? null,
                'pianista_id' => $validatedData['pianista_id'] ?? null,
                'director_himno_id' => $validatedData['director_himno_id'],
                'anuncios' => $validatedData['anuncios'] ?? null,
                'testimonios' => $validatedData['testimonios'] ?? null,
            ]);

            // Eliminar relaciones anteriores y crear nuevas
            $agenda->himnos()->delete();
            $agenda->oraciones()->delete();
            $agenda->mensajes()->delete();
            $agenda->asuntos()->delete();

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

            DB::commit();

            $agenda->load('himnos', 'oraciones', 'mensajes', 'asuntos');

            return response()->json([
                'message' => 'Agenda actualizada exitosamente.',
                'data' => $agenda
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

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

    public function pdf($id)
    {
        $agenda = Agenda::with(['directorHimno', 'presidido', 'dirigido', 'pianista', 'himnos', 'oraciones.miembro', 'mensajes.discursante', 'asuntos'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('agenda.pdf', compact('agenda'));
        $pdf->setPaper('letter', 'portrait');

        $fecha = date('Y-m-d', strtotime($agenda->fecha));
        return $pdf->stream("agenda-sacramental-{$fecha}.pdf");
    }
}
