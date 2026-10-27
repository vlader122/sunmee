<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected ?string $apiUrl;

    protected ?string $apiKey;

    protected ?string $instanceName;

    public function __construct()
    {
        $config = Setting::getWhatsAppConfig();
        $this->apiUrl = $config['api_url'];
        $this->apiKey = $config['api_key'];
        $this->instanceName = $config['instance_name'];
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiUrl)
            && ! empty($this->apiKey)
            && ! empty($this->instanceName);
    }

    public function getInstanceStatus(): array
    {
        if (! $this->isConfigured()) {
            return [
                'connected' => false,
                'message' => 'WhatsApp no configurado',
            ];
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->get("{$this->apiUrl}/instance/connectionState/{$this->instanceName}");

            if ($response->successful()) {
                $data = $response->json();
                $state = $data['instance']['state'] ?? $data['state'] ?? null;

                return [
                    'connected' => $state === 'open',
                    'state' => $state,
                    'message' => $state === 'open' ? 'Conectado' : 'Desconectado',
                ];
            }

            return [
                'connected' => false,
                'message' => 'Error al verificar estado',
            ];
        } catch (\Exception $e) {
            Log::error('Evolution API Error: '.$e->getMessage());

            return [
                'connected' => false,
                'message' => 'Error de conexión: '.$e->getMessage(),
            ];
        }
    }

    public function checkConnection(): array
    {
        return $this->getInstanceStatus();
    }

    public function sendTextMessage(string $phone, string $message): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp no configurado',
            ];
        }

        $phone = $this->formatPhone($phone);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/message/sendText/{$this->instanceName}", [
                'number' => $phone,
                'text' => $message,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Mensaje enviado',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al enviar: '.$response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Evolution API Send Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '52'.$phone;
        }

        return $phone;
    }
}
