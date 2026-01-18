<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configurar WhatsApp - Evolution API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="bg-green-600 text-white rounded-lg p-4 mb-6 shadow-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold">Configurar WhatsApp</h1>
                        <p class="text-green-100 text-sm">Evolution API</p>
                    </div>
                </div>
                <a href="/agenda/crear" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Volver
                </a>
            </div>
        </div>

        <!-- Estado actual -->
        <div id="statusCard" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div id="statusIndicator" class="w-3 h-3 rounded-full bg-gray-300"></div>
                    <span id="statusText" class="text-gray-600">Verificando estado...</span>
                </div>
                <button onclick="checkStatus()" class="text-sm text-blue-600 hover:text-blue-800">
                    Actualizar
                </button>
            </div>
        </div>

        <!-- Formulario -->
        <form id="configForm" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL de Evolution API *</label>
                    <input type="url" name="api_url" id="api_url" required
                        value="{{ $config['api_url'] ?? '' }}"
                        placeholder="https://tu-evolution-api.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-500 mt-1">Ejemplo: https://api.evolution.com</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Key *</label>
                    <input type="password" name="api_key" id="api_key" required
                        value="{{ $config['api_key'] ?? '' }}"
                        placeholder="Tu API Key de Evolution"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Instancia *</label>
                    <input type="text" name="instance_name" id="instance_name" required
                        value="{{ $config['instance_name'] ?? '' }}"
                        placeholder="mi-instancia"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-500 mt-1">El nombre de tu instancia en Evolution API</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" id="btnGuardar"
                    class="flex-1 bg-green-600 text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                    Guardar Configuracion
                </button>
                <button type="button" onclick="testConnection()" id="btnTest"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Probar Conexion
                </button>
            </div>
        </form>

        <!-- Mensaje de resultado -->
        <div id="resultMessage" class="hidden mt-4 p-4 rounded-lg"></div>

        <!-- Ayuda -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-medium text-blue-800 mb-2">Como obtener estos datos?</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>1. Accede a tu panel de Evolution API</li>
                <li>2. Crea una instancia si no tienes una</li>
                <li>3. Escanea el codigo QR con WhatsApp</li>
                <li>4. Copia la URL, API Key y nombre de instancia</li>
            </ul>
        </div>
    </div>

    <script>
        const API_BASE = '/api';

        document.addEventListener('DOMContentLoaded', () => {
            checkStatus();
        });

        async function checkStatus() {
            const indicator = document.getElementById('statusIndicator');
            const text = document.getElementById('statusText');

            indicator.className = 'w-3 h-3 rounded-full bg-yellow-400 animate-pulse';
            text.textContent = 'Verificando...';

            try {
                const response = await fetch(`${API_BASE}/whatsapp/status`);
                const data = await response.json();

                if (!data.configured) {
                    indicator.className = 'w-3 h-3 rounded-full bg-gray-400';
                    text.textContent = 'No configurado';
                } else if (data.connected) {
                    indicator.className = 'w-3 h-3 rounded-full bg-green-500';
                    text.textContent = 'Conectado';
                } else {
                    indicator.className = 'w-3 h-3 rounded-full bg-red-500';
                    text.textContent = data.message || 'Desconectado';
                }
            } catch (error) {
                indicator.className = 'w-3 h-3 rounded-full bg-red-500';
                text.textContent = 'Error al verificar';
            }
        }

        async function testConnection() {
            const btn = document.getElementById('btnTest');
            btn.disabled = true;
            btn.textContent = 'Probando...';

            try {
                const response = await fetch(`${API_BASE}/whatsapp/test`);
                const data = await response.json();

                showResult(data.connected ? 'success' : 'error',
                    data.connected ? 'Conexion exitosa!' : (data.message || 'No se pudo conectar'));
            } catch (error) {
                showResult('error', 'Error al probar conexion');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Probar Conexion';
            }
        }

        document.getElementById('configForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            try {
                const formData = new FormData(this);
                const data = {
                    api_url: formData.get('api_url'),
                    api_key: formData.get('api_key'),
                    instance_name: formData.get('instance_name'),
                };

                const response = await fetch(`${API_BASE}/whatsapp/config`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    showResult('success', 'Configuracion guardada correctamente');
                    checkStatus();
                } else {
                    showResult('error', result.message || 'Error al guardar');
                }
            } catch (error) {
                showResult('error', 'Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Guardar Configuracion';
            }
        });

        function showResult(type, message) {
            const div = document.getElementById('resultMessage');
            div.className = `mt-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
            div.textContent = message;
            div.classList.remove('hidden');

            setTimeout(() => {
                div.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>
