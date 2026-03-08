<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Message;
use App\Models\Pray;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function index() {
        return Member::all();
    }

    public function show($id) {
        $himn = Member::findOrFail($id);
        return $himn;
    }
    public function buscar(Request $request)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:100'
        ]);

        $terminoBusqueda = $request->query('nombre');
        $incluirParticipaciones = $request->query('participaciones', false);

        $query = Member::query();

        if ($terminoBusqueda) {
            $query->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($terminoBusqueda) . '%']);
        }

        $miembros = $query->limit(10)->get();

        if ($incluirParticipaciones) {
            $tresMesesAtras = Carbon::now()->subMonths(3);

            $miembros = $miembros->map(function ($miembro) use ($tresMesesAtras) {
                // Contar discursos en los últimos 3 meses
                $discursos = Message::where('discursante_id', $miembro->id)
                    ->whereHas('agenda', function ($q) use ($tresMesesAtras) {
                        $q->where('fecha', '>=', $tresMesesAtras);
                    })
                    ->count();

                // Contar oraciones en los últimos 3 meses
                $oraciones = Pray::where('miembro_id', $miembro->id)
                    ->whereHas('agenda', function ($q) use ($tresMesesAtras) {
                        $q->where('fecha', '>=', $tresMesesAtras);
                    })
                    ->count();

                // Última participación (discurso u oración)
                $ultimoDiscurso = Message::where('discursante_id', $miembro->id)
                    ->whereHas('agenda')
                    ->with('agenda:id,fecha')
                    ->get()
                    ->sortByDesc(fn($m) => $m->agenda?->fecha)
                    ->first();

                $ultimaOracion = Pray::where('miembro_id', $miembro->id)
                    ->whereHas('agenda')
                    ->with('agenda:id,fecha')
                    ->get()
                    ->sortByDesc(fn($p) => $p->agenda?->fecha)
                    ->first();

                $fechaUltimoDiscurso = $ultimoDiscurso?->agenda?->fecha;
                $fechaUltimaOracion = $ultimaOracion?->agenda?->fecha;

                $ultimaParticipacion = null;
                if ($fechaUltimoDiscurso && $fechaUltimaOracion) {
                    $ultimaParticipacion = max($fechaUltimoDiscurso, $fechaUltimaOracion);
                } else {
                    $ultimaParticipacion = $fechaUltimoDiscurso ?? $fechaUltimaOracion;
                }

                $miembro->participaciones = [
                    'discursos_3m' => $discursos,
                    'oraciones_3m' => $oraciones,
                    'ultima_participacion' => $ultimaParticipacion,
                ];

                return $miembro;
            });
        }

        return response()->json($miembros);
    }

    public function list(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 50);

        $query = Member::query()->orderBy('nombre_completo');

        if ($search) {
            $query->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        return response()->json($query->paginate($perPage));
    }

    public function stats()
    {
        $total = Member::count();
        return response()->json(['total' => $total]);
    }

    public function telefonosActualizados()
    {
        $miembros = Member::whereNotNull('telefono_updated_at')
            ->whereNotNull('telefono')
            ->orderBy('telefono_updated_at', 'desc')
            ->get(['id', 'nombre_completo', 'telefono', 'telefono_updated_at']);

        return response()->json($miembros);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer',
        ]);

        $member->update($request->only(['nombre_completo', 'telefono', 'edad']));

        return response()->json($member);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer',
        ]);

        $member = Member::create($request->only(['nombre_completo', 'telefono', 'edad']));

        return response()->json($member, 201);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Miembro eliminado']);
    }

    public function importar(Request $request)
    {
        $request->validate([
            'nombres' => 'required|array|min:1',
            'nombres.*' => 'required|string|max:255',
        ]);

        $agregados = [];
        $omitidos = [];

        foreach ($request->nombres as $nombre) {
            $nombre = trim($nombre);
            if (!$nombre) continue;

            $existe = Member::whereRaw('LOWER(nombre_completo) = ?', [strtolower($nombre)])->exists();

            if ($existe) {
                $omitidos[] = $nombre;
            } else {
                Member::create(['nombre_completo' => $nombre]);
                $agregados[] = $nombre;
            }
        }

        return response()->json([
            'agregados' => count($agregados),
            'omitidos' => count($omitidos),
            'nombres_agregados' => $agregados,
        ]);
    }

    /**
     * Obtener sugerencias de miembros con teléfono válido, ordenados por menor participación
     */
    public function sugerencias(Request $request)
    {
        $tipo = $request->query('tipo', 'todos'); // 'discursos', 'oraciones', 'todos'
        $limite = min($request->query('limite', 10), 10); // Máximo 10 sugerencias
        $tresMesesAtras = Carbon::now()->subMonths(3);

        // Obtener IDs de miembros con participaciones recientes (solo los necesarios)
        $miembrosConDiscursos = Message::whereHas('agenda', function ($q) use ($tresMesesAtras) {
                $q->where('fecha', '>=', $tresMesesAtras);
            })
            ->selectRaw('discursante_id as miembro_id, COUNT(*) as total')
            ->groupBy('discursante_id')
            ->pluck('total', 'miembro_id');

        $miembrosConOraciones = Pray::whereHas('agenda', function ($q) use ($tresMesesAtras) {
                $q->where('fecha', '>=', $tresMesesAtras);
            })
            ->selectRaw('miembro_id, COUNT(*) as total')
            ->groupBy('miembro_id')
            ->pluck('total', 'miembro_id');

        // Obtener últimas participaciones
        $ultimosDiscursos = Message::whereHas('agenda')
            ->with('agenda:id,fecha')
            ->orderByDesc(
                Agenda::select('fecha')
                    ->whereColumn('agendas.id', 'messages.agenda_id')
                    ->orderBy('fecha', 'desc')
                    ->limit(1)
            )
            ->get()
            ->groupBy('discursante_id')
            ->map(fn($items) => $items->first()->agenda?->fecha);

        $ultimasOraciones = Pray::whereHas('agenda')
            ->with('agenda:id,fecha')
            ->orderByDesc(
                Agenda::select('fecha')
                    ->whereColumn('agendas.id', 'prays.agenda_id')
                    ->orderBy('fecha', 'desc')
                    ->limit(1)
            )
            ->get()
            ->groupBy('miembro_id')
            ->map(fn($items) => $items->first()->agenda?->fecha);

        // Obtener miembros con teléfono válido - limitado desde la BD
        $miembros = Member::whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->whereRaw("telefono ~ '^[67][0-9]{7,}$'") // Solo dígitos, empieza con 6 o 7, 8+ caracteres (PostgreSQL)
            ->limit(100) // Limitar para procesamiento
            ->get()
            ->map(function ($miembro) use ($miembrosConDiscursos, $miembrosConOraciones, $ultimosDiscursos, $ultimasOraciones) {
                $discursos = $miembrosConDiscursos->get($miembro->id, 0);
                $oraciones = $miembrosConOraciones->get($miembro->id, 0);

                $fechaUltimoDiscurso = $ultimosDiscursos->get($miembro->id);
                $fechaUltimaOracion = $ultimasOraciones->get($miembro->id);

                $ultimaParticipacion = null;
                if ($fechaUltimoDiscurso && $fechaUltimaOracion) {
                    $ultimaParticipacion = max($fechaUltimoDiscurso, $fechaUltimaOracion);
                } else {
                    $ultimaParticipacion = $fechaUltimoDiscurso ?? $fechaUltimaOracion;
                }

                $miembro->discursos_3m = $discursos;
                $miembro->oraciones_3m = $oraciones;
                $miembro->total_3m = $discursos + $oraciones;
                $miembro->ultima_participacion = $ultimaParticipacion;

                return $miembro;
            });

        // Ordenar por menor participación (primero los que no han participado)
        $ordenados = $miembros->sortBy([
            ['total_3m', 'asc'],
            ['ultima_participacion', 'asc'],
            ['nombre_completo', 'asc']
        ])->take($limite);

        return response()->json($ordenados->values());
    }
}
