<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Agendas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="bg-blue-600 text-white rounded-lg p-4 mb-6 shadow-md flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Historial de Agendas</h1>
                <p class="text-blue-100 text-sm">Consulta las agendas sacramentales anteriores</p>
            </div>
            <a href="/agenda/crear" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                + Nueva Agenda
            </a>
        </div>

        <!-- Lista de Agendas -->
        <div id="agendasContainer" class="space-y-4">
            <div class="text-center py-8 text-gray-500">Cargando agendas...</div>
        </div>

        <!-- Paginación -->
        <div id="paginacion" class="flex justify-center gap-2 mt-6"></div>

        <!-- Modal Detalle -->
        <div id="modalDetalle" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                    <h3 class="text-xl font-bold" id="modalTitulo">Agenda</h3>
                    <div class="flex items-center gap-2">
                        <a id="modalPdfBtn" href="#" target="_blank" class="bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            PDF
                        </a>
                        <button onclick="cerrarModalDetalle()" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="modalContenido" class="p-4"></div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api';
        let paginaActual = 1;

        document.addEventListener('DOMContentLoaded', () => {
            cargarAgendas();
        });

        async function cargarAgendas(pagina = 1) {
            paginaActual = pagina;
            const container = document.getElementById('agendasContainer');

            try {
                const response = await fetch(`${API_BASE}/agendas?page=${pagina}`);
                const data = await response.json();

                if (!data.data || data.data.length === 0) {
                    container.innerHTML = `
                        <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">No hay agendas</h3>
                            <p class="text-gray-500 mb-4">Crea tu primera agenda sacramental</p>
                            <a href="/agenda/crear" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Crear Agenda
                            </a>
                        </div>
                    `;
                    document.getElementById('paginacion').innerHTML = '';
                    return;
                }

                container.innerHTML = data.data.map(agenda => `
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow ${agenda.tipo_agenda_id == 1 ? 'border-l-4 border-l-amber-400' : ''}">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-lg font-bold text-gray-800">${formatearFecha(agenda.fecha)}</span>
                                        ${agenda.tipo_agenda_id == 1 ? `<span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-medium">Ayuno</span>` :
                                          agenda.tipo_agenda_id ? `<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">${getTipoAgenda(agenda.tipo_agenda_id)}</span>` : ''}
                                    </div>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        ${agenda.director_himno ? `<p><span class="text-gray-400">Director:</span> ${agenda.director_himno.nombre_completo}</p>` : ''}
                                        ${agenda.presidido ? `<p><span class="text-gray-400">Preside:</span> ${agenda.presidido.nombre_completo}</p>` : ''}
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs mt-2">
                                        ${agenda.himnos?.length ? `<span class="px-2 py-1 bg-green-100 text-green-700 rounded">${agenda.himnos.length} himnos</span>` : ''}
                                        ${agenda.tipo_agenda_id == 1 && agenda.testimonios ? `<span class="px-2 py-1 bg-amber-100 text-amber-700 rounded">Testimonios</span>` : ''}
                                        ${agenda.mensajes?.length ? `<span class="px-2 py-1 bg-orange-100 text-orange-700 rounded">${agenda.mensajes.length} discursos</span>` : ''}
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 ml-4">
                                    <button onclick="verDetalle(${agenda.id})" class="flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver
                                    </button>
                                    <a href="/agenda/editar/${agenda.id}" class="flex items-center gap-2 px-3 py-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Editar
                                    </a>
                                    <a href="/agenda/pdf/${agenda.id}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Paginación
                renderPaginacion(data);

            } catch (error) {
                container.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center text-red-600">
                        Error al cargar las agendas: ${error.message}
                    </div>
                `;
            }
        }

        function renderPaginacion(data) {
            const pag = document.getElementById('paginacion');
            if (data.last_page <= 1) {
                pag.innerHTML = '';
                return;
            }

            let html = '';
            if (data.current_page > 1) {
                html += `<button onclick="cargarAgendas(${data.current_page - 1})" class="px-4 py-2 bg-white border rounded hover:bg-gray-50">Anterior</button>`;
            }

            for (let i = 1; i <= data.last_page; i++) {
                const active = i === data.current_page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50';
                html += `<button onclick="cargarAgendas(${i})" class="px-4 py-2 border rounded ${active}">${i}</button>`;
            }

            if (data.current_page < data.last_page) {
                html += `<button onclick="cargarAgendas(${data.current_page + 1})" class="px-4 py-2 bg-white border rounded hover:bg-gray-50">Siguiente</button>`;
            }

            pag.innerHTML = html;
        }

        async function verDetalle(id) {
            try {
                const response = await fetch(`${API_BASE}/agendas/${id}`);
                const agenda = await response.json();

                document.getElementById('modalTitulo').textContent = `Agenda - ${formatearFecha(agenda.fecha)}`;
                document.getElementById('modalPdfBtn').href = `/agenda/pdf/${id}`;

                let html = `
                    <div class="space-y-4">
                        <!-- Info General -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            ${agenda.presidido ? `<div><span class="text-gray-500">Preside:</span><br><strong>${agenda.presidido.nombre_completo}</strong></div>` : ''}
                            ${agenda.dirigido ? `<div><span class="text-gray-500">Dirige:</span><br><strong>${agenda.dirigido.nombre_completo}</strong></div>` : ''}
                            ${agenda.director_himno ? `<div><span class="text-gray-500">Director de Música:</span><br><strong>${agenda.director_himno.nombre_completo}</strong></div>` : ''}
                            ${agenda.pianista ? `<div><span class="text-gray-500">Pianista:</span><br><strong>${agenda.pianista.nombre_completo}</strong></div>` : ''}
                        </div>
                `;

                // Himnos
                if (agenda.himnos?.length) {
                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-2 text-xs">H</span>
                                Himnos
                            </h4>
                            <div class="space-y-1 text-sm">
                                ${agenda.himnos.map(h => `
                                    <div class="flex justify-between bg-gray-50 px-3 py-2 rounded">
                                        <span class="capitalize text-gray-500">${h.tipo || 'Himno'}</span>
                                        <span><strong>#${h.numero}</strong> ${h.titulo || ''}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                // Oraciones
                if (agenda.oraciones?.length) {
                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <span class="w-6 h-6 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-2 text-xs">O</span>
                                Oraciones
                            </h4>
                            <div class="space-y-1 text-sm">
                                ${agenda.oraciones.map(o => `
                                    <div class="flex justify-between bg-gray-50 px-3 py-2 rounded">
                                        <span class="capitalize text-gray-500">${o.tipo}</span>
                                        <span><strong>${o.miembro?.nombre_completo || 'Sin asignar'}</strong></span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                // Discursos (solo si no es ayuno)
                if (agenda.mensajes?.length && agenda.tipo_agenda_id != 1) {
                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <span class="w-6 h-6 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-2 text-xs">D</span>
                                Discursos
                            </h4>
                            <div class="space-y-2 text-sm">
                                ${agenda.mensajes.map((m, i) => `
                                    <div class="bg-gray-50 px-3 py-2 rounded">
                                        <div class="flex justify-between">
                                            <span class="font-medium">${m.discursante?.nombre_completo || 'Sin asignar'}</span>
                                            <span class="text-gray-400">#${i + 1}</span>
                                        </div>
                                        <div class="text-gray-500 text-xs mt-1">Tema: ${m.tema || 'Sin tema'}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                // Testimonios (solo reunión de ayuno)
                if (agenda.testimonios && agenda.tipo_agenda_id == 1) {
                    const testimoniosList = agenda.testimonios.split('\n').filter(t => t.trim());
                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <span class="w-6 h-6 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mr-2 text-xs">T</span>
                                Testimonios
                            </h4>
                            <div class="space-y-1 text-sm">
                                ${testimoniosList.map((t, i) => `
                                    <div class="flex items-center gap-2 bg-amber-50 px-3 py-2 rounded">
                                        <span class="w-5 h-5 bg-amber-200 text-amber-700 rounded-full flex items-center justify-center text-xs">${i + 1}</span>
                                        <span>${t}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }

                // Asuntos del Barrio organizados por tipo
                if (agenda.asuntos?.length) {
                    const relevos = agenda.asuntos.filter(a => a.tipo_asunto === 'relevos');
                    const sostenimientos = agenda.asuntos.filter(a => a.tipo_asunto === 'sostenimientos');
                    const presentaciones = agenda.asuntos.filter(a => a.tipo_asunto === 'presentaciones');
                    const otros = agenda.asuntos.filter(a => !a.tipo_asunto || !['relevos', 'sostenimientos', 'presentaciones'].includes(a.tipo_asunto));

                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <span class="w-6 h-6 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-2 text-xs">A</span>
                                Asuntos del Barrio
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                ${relevos.length ? `
                                    <div class="border border-red-200 rounded-lg p-2">
                                        <h5 class="font-medium text-red-700 mb-1 text-xs flex items-center">
                                            <span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>Relevos
                                        </h5>
                                        <ul class="space-y-1">${relevos.map(a => `<li class="text-gray-700 text-xs">${a.descripcion}</li>`).join('')}</ul>
                                    </div>
                                ` : ''}
                                ${sostenimientos.length ? `
                                    <div class="border border-green-200 rounded-lg p-2">
                                        <h5 class="font-medium text-green-700 mb-1 text-xs flex items-center">
                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>Sostenimientos
                                        </h5>
                                        <ul class="space-y-1">${sostenimientos.map(a => `<li class="text-gray-700 text-xs">${a.descripcion}</li>`).join('')}</ul>
                                    </div>
                                ` : ''}
                                ${presentaciones.length ? `
                                    <div class="border border-blue-200 rounded-lg p-2">
                                        <h5 class="font-medium text-blue-700 mb-1 text-xs flex items-center">
                                            <span class="w-2 h-2 bg-blue-400 rounded-full mr-1"></span>Presentaciones
                                        </h5>
                                        <ul class="space-y-1">${presentaciones.map(a => `<li class="text-gray-700 text-xs">${a.descripcion}</li>`).join('')}</ul>
                                    </div>
                                ` : ''}
                            </div>
                            ${otros.length ? `
                                <div class="mt-2">
                                    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                        ${otros.map(a => `<li>${a.descripcion}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }

                // Anuncios
                if (agenda.anuncios) {
                    html += `
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center mr-2 text-xs">N</span>
                                Anuncios
                            </h4>
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded whitespace-pre-line">${agenda.anuncios}</p>
                        </div>
                    `;
                }

                html += '</div>';

                document.getElementById('modalContenido').innerHTML = html;
                document.getElementById('modalDetalle').classList.remove('hidden');
                document.getElementById('modalDetalle').classList.add('flex');

            } catch (error) {
                alert('Error al cargar el detalle: ' + error.message);
            }
        }

        function cerrarModalDetalle() {
            document.getElementById('modalDetalle').classList.add('hidden');
            document.getElementById('modalDetalle').classList.remove('flex');
        }

        function formatearFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            // Manejar diferentes formatos de fecha
            let dateStr = fecha;
            // Si viene con hora (ej: "2025-01-05 00:00:00"), tomar solo la parte de fecha
            if (fecha.includes(' ')) {
                dateStr = fecha.split(' ')[0];
            }
            // Si viene como ISO completo, tomar solo la parte de fecha
            if (fecha.includes('T')) {
                dateStr = fecha.split('T')[0];
            }
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const date = new Date(dateStr + 'T12:00:00');
            if (isNaN(date.getTime())) return fecha; // Si aun es invalida, devolver el string original
            return date.toLocaleDateString('es-ES', options);
        }

        function getTipoAgenda(tipo) {
            const tipos = { 1: 'Ayuno', 2: 'Conf. Estaca', 3: 'Especial' };
            return tipos[tipo] || 'Regular';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalDetalle').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalDetalle();
        });
    </script>
</body>
</html>
