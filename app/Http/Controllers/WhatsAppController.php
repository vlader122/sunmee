<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Setting;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    public function config()
    {
        $config = Setting::getWhatsAppConfig();
        return view('whatsapp.config', compact('config'));
    }

    public function status()
    {
        $service = new EvolutionApiService();

        if (!$service->isConfigured()) {
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

        $service = new EvolutionApiService();
        $status = $service->checkConnection();

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada',
            'connection' => $status,
        ]);
    }

    public function testConnection()
    {
        $service = new EvolutionApiService();
        $status = $service->checkConnection();

        return response()->json($status);
    }

    public function sendNotifications(int $agendaId)
    {
        $agenda = Agenda::with([
            'mensajes.discursante',
            'oraciones.miembro'
        ])->findOrFail($agendaId);

        $service = new EvolutionApiService();

        if (!$service->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'WhatsApp no configurado',
            ], 400);
        }

        $status = $service->getInstanceStatus();
        if (!$status['connected']) {
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
            if (!$mensaje->discursante || !$mensaje->discursante->telefono) {
                $results['skipped'][] = [
                    'tipo' => 'discurso',
                    'nombre' => $mensaje->discursante?->nombre_completo ?? 'Sin asignar',
                    'reason' => 'Sin teléfono',
                ];
                continue;
            }

            $text = "Hola {$mensaje->discursante->nombre_completo}, te recordamos que tienes asignado un discurso para la reunión sacramental del {$fecha}.\n\n";
            if ($mensaje->tema) {
                $text .= "Tema: {$mensaje->tema}\n\n";
            }
            $text .= "Gracias por tu servicio!";

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
            if (!$oracion->miembro || !$oracion->miembro->telefono) {
                $results['skipped'][] = [
                    'tipo' => 'oracion',
                    'nombre' => $oracion->miembro?->nombre_completo ?? 'Sin asignar',
                    'reason' => 'Sin teléfono',
                ];
                continue;
            }

            $tipoOracion = $oracion->tipo === 'primera' ? 'primera' : 'última';
            $text = "Hola {$oracion->miembro->nombre_completo}, te recordamos que tienes asignada la {$tipoOracion} oración para la reunión sacramental del {$fecha}.\n\n";
            $text .= "Gracias por tu servicio!";

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
