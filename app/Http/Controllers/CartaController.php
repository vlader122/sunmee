<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaController extends Controller
{
    /**
     * Mostrar la vista del generador de cartas
     */
    public function index()
    {
        return view('cartas.generar');
    }

    /**
     * Obtener asignaciones en un rango de fechas
     */
    public function getAsignaciones(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        // Obtener agendas en el rango con sus relaciones
        $agendas = Agenda::with([
            'mensajes.discursante',
            'oraciones.miembro',
            'presidido',
            'dirigido'
        ])
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha', 'asc')
            ->get();

        $asignaciones = [];

        foreach ($agendas as $agenda) {
            // Procesar mensajes/discursos
            foreach ($agenda->mensajes as $mensaje) {
                $nombre = $mensaje->discursante?->nombre_completo ?? $mensaje->discursante_nombre;
                if ($nombre) {
                    $asignaciones[] = [
                        'id' => 'msg_' . $mensaje->id,
                        'fecha' => $agenda->fecha,
                        'fecha_formateada' => $this->formatearFecha($agenda->fecha),
                        'tipo' => 'Discurso',
                        'tema' => $mensaje->tema,
                        'tiempo' => $mensaje->tiempo ?: '15 minutos',
                        'destinatario' => $nombre,
                        'tipo_agenda' => $this->getTipoAgenda($agenda->tipo_agenda_id),
                    ];
                }
            }

            // Procesar oraciones
            foreach ($agenda->oraciones as $oracion) {
                if ($oracion->miembro) {
                    $tipoOracion = $this->getTipoOracion($oracion->tipo);
                    $asignaciones[] = [
                        'id' => 'pray_' . $oracion->id,
                        'fecha' => $agenda->fecha,
                        'fecha_formateada' => $this->formatearFecha($agenda->fecha),
                        'tipo' => $tipoOracion,
                        'tema' => null,
                        'tiempo' => null,
                        'destinatario' => $oracion->miembro->nombre_completo,
                        'tipo_agenda' => $this->getTipoAgenda($agenda->tipo_agenda_id),
                    ];
                }
            }
        }

        return response()->json([
            'asignaciones' => $asignaciones,
            'total' => count($asignaciones),
        ]);
    }

    /**
     * Generar PDF de previsualización (temporal)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'asignaciones' => 'required|array',
            'asignaciones.*.destinatario' => 'required|string',
            'asignaciones.*.tipo' => 'required|string',
            'asignaciones.*.fecha' => 'required|date',
        ]);

        $asignaciones = collect($request->input('asignaciones'));
        $preview = $request->input('preview', false);

        $pdf = Pdf::loadView('cartas.carta-template', [
            'paginas' => $asignaciones,
            'preview' => $preview,
        ]);

        $pdf->setPaper('letter', 'portrait');

        if ($preview) {
            // Para iframe preview
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="preview.pdf"');
        }

        // Para descarga
        $fecha = date('Y-m-d');
        return $pdf->download("cartas-asignacion-{$fecha}.pdf");
    }

    /**
     * Generar PDF final
     */
    public function generarPDF(Request $request)
    {
        $request->validate([
            'asignaciones' => 'required|array',
            'asignaciones.*.destinatario' => 'required|string',
            'asignaciones.*.tipo' => 'required|string',
            'asignaciones.*.fecha' => 'required|date',
            'asignaciones.*.tema' => 'nullable|string',
            'asignaciones.*.tiempo' => 'nullable|string',
        ]);

        $asignaciones = collect($request->input('asignaciones'));

        $pdf = Pdf::loadView('cartas.carta-template', [
            'paginas' => $asignaciones,
            'preview' => false,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $fecha = date('Y-m-d-His');
        return $pdf->download("cartas-asignacion-{$fecha}.pdf");
    }

    /**
     * Formatear fecha en español
     */
    private function formatearFecha($fecha)
    {
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish');
        $date = new \DateTime($fecha);
        return strftime('%A, %d de %B de %Y', $date->getTimestamp());
    }

    /**
     * Obtener tipo de agenda
     */
    private function getTipoAgenda($tipo)
    {
        return match ($tipo) {
            1 => 'Ayuno y Testimonio',
            2 => 'Conferencia de Estaca',
            3 => 'Reunión Especial',
            default => 'Reunión Sacramental',
        };
    }

    /**
     * Obtener tipo de oración legible
     */
    private function getTipoOracion($tipo)
    {
        if (empty($tipo)) {
            return 'Oración';
        }
        
        return match (strtolower($tipo)) {
            'apertura', 'primera', 'invocacion', 'invocación' => 'Oración de Apertura',
            'cierre', 'ultima', 'última', 'bendicion', 'bendición' => 'Oración de Cierre',
            default => 'Oración',
        };
    }
}
