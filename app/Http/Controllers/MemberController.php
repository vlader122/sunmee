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
                    ->orderByDesc(Agenda::select('fecha')->whereColumn('agendas.id', 'messages.agenda_id'))
                    ->first();

                $ultimaOracion = Pray::where('miembro_id', $miembro->id)
                    ->whereHas('agenda')
                    ->with('agenda:id,fecha')
                    ->orderByDesc(Agenda::select('fecha')->whereColumn('agendas.id', 'prays.agenda_id'))
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

    /**
     * Obtener sugerencias de miembros con teléfono válido, ordenados por menor participación
     */
    public function sugerencias(Request $request)
    {
        $tipo = $request->query('tipo', 'todos'); // 'discursos', 'oraciones', 'todos'
        $limite = $request->query('limite', 15);
        $tresMesesAtras = Carbon::now()->subMonths(3);

        // Obtener miembros con teléfono válido
        $miembros = Member::whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->get()
            ->filter(function ($m) {
                $tel = trim($m->telefono);
                // Validar: solo dígitos, 8+ caracteres, empieza con 6 o 7
                if (!preg_match('/^\d+$/', $tel)) return false;
                if (strlen($tel) < 8) return false;
                if (!in_array($tel[0], ['6', '7'])) return false;
                return true;
            });

        // Calcular participaciones para cada miembro
        $miembrosConParticipacion = $miembros->map(function ($miembro) use ($tresMesesAtras, $tipo) {
            $discursos = Message::where('discursante_id', $miembro->id)
                ->whereHas('agenda', function ($q) use ($tresMesesAtras) {
                    $q->where('fecha', '>=', $tresMesesAtras);
                })
                ->count();

            $oraciones = Pray::where('miembro_id', $miembro->id)
                ->whereHas('agenda', function ($q) use ($tresMesesAtras) {
                    $q->where('fecha', '>=', $tresMesesAtras);
                })
                ->count();

            // Última participación
            $ultimoDiscurso = Message::where('discursante_id', $miembro->id)
                ->whereHas('agenda')
                ->with('agenda:id,fecha')
                ->orderByDesc(Agenda::select('fecha')->whereColumn('agendas.id', 'messages.agenda_id'))
                ->first();

            $ultimaOracion = Pray::where('miembro_id', $miembro->id)
                ->whereHas('agenda')
                ->with('agenda:id,fecha')
                ->orderByDesc(Agenda::select('fecha')->whereColumn('agendas.id', 'prays.agenda_id'))
                ->first();

            $fechaUltimoDiscurso = $ultimoDiscurso?->agenda?->fecha;
            $fechaUltimaOracion = $ultimaOracion?->agenda?->fecha;

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
        $ordenados = $miembrosConParticipacion->sortBy([
            ['total_3m', 'asc'],
            ['ultima_participacion', 'asc'],
            ['nombre_completo', 'asc']
        ])->take($limite);

        return response()->json($ordenados->values());
    }
}
