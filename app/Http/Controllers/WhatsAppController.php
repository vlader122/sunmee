<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Setting;
use App\Services\EvolutionApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function config()
    {
        $config = Setting::getWhatsAppConfig();

        return view('whatsapp.config', compact('config'));
    }

    public function status()
    {
        $service = new EvolutionApiService;

        if (! $service->isConfigured()) {
            return response()->json([
                'configured' => false,
                'connected' => false,
                'message' => 'WhatsApp no configurado',
            ]);
        }

        $status = $service->getInstanceStatus();

        return response()->json([
            'configured' => true,
            'connected' => $status['connected'],
            'message' => $status['message'],
            'state' => $status['state'] ?? null,
        ]);
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'instance_name' => 'required|string',
        ]);

        $apiUrl = rtrim($request->api_url, '/');

        Setting::set('evolution_api_url', $apiUrl);
        Setting::set('evolution_api_key', $request->api_key);
        Setting::set('evolution_instance_name', $request->instance_name);

        $service = new EvolutionApiService;
        $status = $service->checkConnection();

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada',
            'connection' => $status,
        ]);
    }

    public function testConnection()
    {
        $service = new EvolutionApiService;
        $status = $service->checkConnection();

        return response()->json($status);
    }

    public function sendNotifications(int $agendaId)
    {
        $agenda = Agenda::with([
            'mensajes.discursante',
            'oraciones.miembro',
        ])->findOrFail($agendaId);

        $service = new EvolutionApiService;

        if (! $service->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp no configurado',
            ], 400);
        }

        $status = $service->getInstanceStatus();
        if (! $status['connected']) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp no está conectado',
            ], 400);
        }

        $fecha = Carbon::parse($agenda->fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $results = [
            'sent' => [],
            'failed' => [],
            'skipped' => [],
        ];

        foreach ($agenda->mensajes as $mensaje) {
            if (! $mensaje->discursante || ! $mensaje->discursante->telefono) {
                $results['skipped'][] = [
                    'tipo' => 'discurso',
                    'nombre' => $mensaje->discursante?->nombre_completo ?? 'Sin asignar',
                    'reason' => 'Sin teléfono',
                ];

                continue;
            }

            $nombre = $mensaje->discursante->nombre_completo;
            $tema = $mensaje->tema ?: 'Por confirmar';

            $text = "*ASIGNACIÓN DE DISCURSO*\n";
            $text .= "Barrio Terminal - Estaca Oruro\n\n";
            $text .= "Hno(a). {$nombre}\n";
            $text .= "Presente.\n\n";
            $text .= "El Obispado del Barrio Terminal, en la Estaca Oruro, le saluda cordialmente, esperando que la alegría de vivir el evangelio se refleje en usted y toda su familia.\n\n";
            $text .= "Se le notifica que ha sido asignado(a) para discursar en la reunión sacramental del día *domingo {$fecha}*, con el tema:\n\n";
            $text .= "📌 *{$tema}*\n\n";
            $text .= "Se requiere que esté con 10 minutos de anticipación en el estrado.\n\n";
            $text .= "Si existiera algún inconveniente, por favor notifique al secretario de barrio al menos 3 días antes de la reunión.\n\n";
            $text .= "Rogamos que el Señor le prepare y fortalezca para hacer de esta reunión una experiencia espiritual para todos.\n\n";
            $text .= "Atentamente,\n";
            $text .= '_El Obispado_';

            $result = $service->sendTextMessage($mensaje->discursante->telefono, $text);

            if ($result['success']) {
                $results['sent'][] = [
                    'tipo' => 'discurso',
                    'nombre' => $mensaje->discursante->nombre_completo,
                ];
            } else {
                $results['failed'][] = [
                    'tipo' => 'discurso',
                    'nombre' => $mensaje->discursante->nombre_completo,
                    'error' => $result['message'],
                ];
            }
        }

        foreach ($agenda->oraciones as $oracion) {
            if (! $oracion->miembro || ! $oracion->miembro->telefono) {
                $results['skipped'][] = [
                    'tipo' => 'oracion',
                    'nombre' => $oracion->miembro?->nombre_completo ?? $oracion->orante_nombre ?? 'Sin asignar',
                    'reason' => 'Sin teléfono',
                ];

                continue;
            }

            $nombre = $oracion->miembro->nombre_completo;
            $tipoOracion = $oracion->tipo === 'primera' ? 'primera' : 'última';

            $text = "*ASIGNACIÓN DE ORACIÓN*\n";
            $text .= "Barrio Terminal - Estaca Oruro\n\n";
            $text .= "Hno(a). {$nombre}\n";
            $text .= "Presente.\n\n";
            $text .= "El Obispado del Barrio Terminal, en la Estaca Oruro, le saluda cordialmente.\n\n";
            $text .= "Se le notifica que ha sido asignado(a) para ofrecer la *{$tipoOracion} oración* en la reunión sacramental del día *domingo {$fecha}*.\n\n";
            $text .= "Se requiere que esté con 10 minutos de anticipación.\n\n";
            $text .= "Si existiera algún inconveniente, por favor notifique al secretario de barrio al menos 3 días antes de la reunión.\n\n";
            $text .= "Atentamente,\n";
            $text .= '_El Obispado_';

            $result = $service->sendTextMessage($oracion->miembro->telefono, $text);

            if ($result['success']) {
                $results['sent'][] = [
                    'tipo' => 'oracion',
                    'nombre' => $oracion->miembro->nombre_completo,
                ];
            } else {
                $results['failed'][] = [
                    'tipo' => 'oracion',
                    'nombre' => $oracion->miembro->nombre_completo,
                    'error' => $result['message'],
                ];
            }
        }

        $totalSent = count($results['sent']);
        $totalFailed = count($results['failed']);
        $totalSkipped = count($results['skipped']);

        return response()->json([
            'success' => true,
            'message' => "Enviados: {$totalSent}, Fallidos: {$totalFailed}, Omitidos: {$totalSkipped}",
            'results' => $results,
        ]);
    }
}
