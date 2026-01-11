<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Agenda Sacramental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .autocomplete-items { position: absolute; z-index: 99; top: 100%; left: 0; right: 0; }
        .autocomplete-items div { padding: 8px; cursor: pointer; background: white; border-bottom: 1px solid #e5e7eb; }
        .autocomplete-items div:hover, .autocomplete-active { background: #dbeafe !important; }
        input:focus, select:focus, textarea:focus { outline: none; box-shadow: 0 0 0 2px #3b82f6; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-6 px-4">
        <!-- Header -->
        <div class="bg-amber-600 text-white rounded-lg p-4 mb-6 shadow-md flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Editar Agenda Sacramental</h1>
                <p class="text-amber-100 text-sm">Modifica los campos de la agenda</p>
            </div>
            <div class="flex gap-2">
                <a href="/agenda/historial" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Ver Historial
                </a>
                <a href="/agenda/crear" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Nueva Agenda
                </a>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-4 text-center">
            <div class="animate-spin w-8 h-8 border-4 border-amber-600 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600">Cargando agenda...</p>
        </div>

        <!-- Indicador de tipo de reunion -->
        <div id="tipoReunionBanner" class="hidden bg-amber-100 border border-amber-300 text-amber-800 rounded-lg p-3 mb-4 text-center font-medium">
            Reunion de Ayuno y Testimonios - Primer Domingo del Mes
        </div>

        <form id="agendaForm" class="hidden">
            <!-- Fecha y Tipo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de la Reunion (Domingo) *</label>
                        <input type="date" name="fecha" id="fechaInput" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-lg">
                        <p class="text-xs text-gray-500 mt-1">Solo se permiten domingos</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Reunion</label>
                        <select name="tipo_agenda_id" id="tipoAgendaSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Regular</option>
                            <option value="1">Ayuno</option>
                            <option value="2">Conferencia de Estaca</option>
                            <option value="3">Especial</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Anuncios -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center mr-2 text-sm">1</span>
                    Anuncios
                </h2>
                <textarea name="anuncios" rows="3" placeholder="Escribe los anuncios para la reunion..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm resize-none"></textarea>
            </div>

            <!-- Liderazgo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-2 text-sm">2</span>
                    Presidencia y Direccion
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preside</label>
                        <input type="text" data-member-input data-target="presidido_por_id" placeholder="Buscar miembro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="presidido_por_id">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirige</label>
                        <input type="text" data-member-input data-target="dirigido_por_id" placeholder="Buscar miembro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="dirigido_por_id">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Director de Musica *</label>
                        <input type="text" data-member-input data-target="director_himno_id" placeholder="Buscar miembro..." required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="director_himno_id" required>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pianista</label>
                        <input type="text" data-member-input data-target="pianista_id" placeholder="Buscar miembro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="pianista_id">
                    </div>
                </div>
            </div>

            <!-- Himnos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-2 text-sm">3</span>
                    Himnos
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex gap-2">
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Apertura</label>
                            <input type="number" name="himno_apertura_num" placeholder="#" min="1" max="341"
                                class="w-full px-2 py-2 border border-gray-300 rounded-md text-center" data-himno-num="apertura">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                            <input type="text" name="himno_apertura_titulo" placeholder="Titulo del himno" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50" data-himno-titulo="apertura">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Sacramental</label>
                            <input type="number" name="himno_sacramental_num" placeholder="#" min="1" max="341"
                                class="w-full px-2 py-2 border border-gray-300 rounded-md text-center" data-himno-num="sacramental">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                            <input type="text" name="himno_sacramental_titulo" placeholder="Titulo del himno" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50" data-himno-titulo="sacramental">
                        </div>
                    </div>
                    <div class="flex gap-2" id="himnoIntermedioContainer">
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Intermedio</label>
                            <input type="number" name="himno_intermedio_num" placeholder="#" min="1" max="341"
                                class="w-full px-2 py-2 border border-gray-300 rounded-md text-center" data-himno-num="intermedio">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                            <input type="text" name="himno_intermedio_titulo" placeholder="Titulo del himno" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50" data-himno-titulo="intermedio">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Cierre</label>
                            <input type="number" name="himno_cierre_num" placeholder="#" min="1" max="341"
                                class="w-full px-2 py-2 border border-gray-300 rounded-md text-center" data-himno-num="cierre">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                            <input type="text" name="himno_cierre_titulo" placeholder="Titulo del himno" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50" data-himno-titulo="cierre">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Oraciones -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-2 text-sm">4</span>
                    Oraciones
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primera Oracion</label>
                        <input type="text" data-member-input data-target="oracion_primera_id" placeholder="Buscar miembro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="oracion_primera_id">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ultima Oracion</label>
                        <input type="text" data-member-input data-target="oracion_ultima_id" placeholder="Buscar miembro..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md" autocomplete="off">
                        <input type="hidden" name="oracion_ultima_id">
                    </div>
                </div>
            </div>

            <!-- Discursos (Solo reunion regular) -->
            <div id="seccionDiscursos" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-2 text-sm">5</span>
                    Discursos
                </h2>
                <div id="discursosContainer" class="space-y-3"></div>
                <button type="button" onclick="agregarDiscurso()"
                    class="mt-3 w-full py-2 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:border-blue-400 hover:text-blue-500 transition-colors">
                    + Agregar Discurso
                </button>
            </div>

            <!-- Testimonios (Solo reunion de ayuno) -->
            <div id="seccionTestimonios" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mr-2 text-sm">5</span>
                    Testimonios
                </h2>
                <p class="text-sm text-gray-500 mb-3">Agrega los nombres de quienes compartieron su testimonio</p>
                <div id="testimoniosContainer" class="space-y-2 mb-3"></div>
                <button type="button" onclick="agregarTestimonio()"
                    class="w-full py-2 border-2 border-dashed border-gray-300 rounded-md text-gray-500 hover:border-amber-400 hover:text-amber-500 transition-colors">
                    + Agregar Testimonio
                </button>
                <input type="hidden" name="testimonios" id="testimoniosHidden">
            </div>

            <!-- Asuntos del Barrio - 3 Columnas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-2 text-sm">6</span>
                    Asuntos del Barrio
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Relevos -->
                    <div class="border border-gray-200 rounded-lg p-3">
                        <h3 class="font-medium text-gray-700 mb-2 text-sm flex items-center">
                            <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                            Relevos
                        </h3>
                        <div id="relevosContainer" class="space-y-2 mb-2"></div>
                        <button type="button" onclick="agregarAsunto('relevos')"
                            class="w-full py-1.5 text-xs border border-dashed border-gray-300 rounded text-gray-500 hover:border-red-400 hover:text-red-500">
                            + Agregar
                        </button>
                    </div>

                    <!-- Sostenimientos -->
                    <div class="border border-gray-200 rounded-lg p-3">
                        <h3 class="font-medium text-gray-700 mb-2 text-sm flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                            Sostenimientos
                        </h3>
                        <div id="sostenimientosContainer" class="space-y-2 mb-2"></div>
                        <button type="button" onclick="agregarAsunto('sostenimientos')"
                            class="w-full py-1.5 text-xs border border-dashed border-gray-300 rounded text-gray-500 hover:border-green-400 hover:text-green-500">
                            + Agregar
                        </button>
                    </div>

                    <!-- Presentaciones -->
                    <div class="border border-gray-200 rounded-lg p-3">
                        <h3 class="font-medium text-gray-700 mb-2 text-sm flex items-center">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mr-2"></span>
                            Presentaciones
                        </h3>
                        <p class="text-xs text-gray-400 mb-2">Conversos y bendiciones</p>
                        <div id="presentacionesContainer" class="space-y-2 mb-2"></div>
                        <button type="button" onclick="agregarAsunto('presentaciones')"
                            class="w-full py-1.5 text-xs border border-dashed border-gray-300 rounded text-gray-500 hover:border-blue-400 hover:text-blue-500">
                            + Agregar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 mt-6">
                <button type="submit" id="btnGuardar"
                    class="flex-1 bg-amber-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-amber-700 transition-colors shadow-md">
                    Actualizar Agenda
                </button>
                <button type="button" onclick="eliminarAgenda()"
                    class="px-6 py-3 bg-red-100 text-red-600 rounded-lg font-medium hover:bg-red-200 transition-colors">
                    Eliminar
                </button>
                <a href="/agenda/historial"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors text-center">
                    Cancelar
                </a>
            </div>
        </form>

        <!-- Modal de exito -->
        <div id="modalExito" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm mx-4 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Agenda Actualizada</h3>
                <p class="text-gray-600 mb-4">Los cambios se han guardado correctamente.</p>
                <a href="/agenda/historial" class="block w-full bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">
                    Volver al Historial
                </a>
            </div>
        </div>

        <!-- Modal de confirmacion eliminar -->
        <div id="modalEliminar" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm mx-4 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Eliminar Agenda</h3>
                <p class="text-gray-600 mb-4">Esta accion no se puede deshacer. La agenda sera eliminada permanentemente.</p>
                <div class="flex gap-3">
                    <button onclick="cerrarModalEliminar()" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button onclick="confirmarEliminar()" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/api';
        const AGENDA_ID = {{ $agendaId }};
        let discursoCount = 0;
        let testimonioCount = 0;
        let asuntoCounters = { relevos: 0, sostenimientos: 0, presentaciones: 0 };
        let debounceTimer;
        let esAyuno = false;
        let agendaData = null;

        // Inicializacion
        document.addEventListener('DOMContentLoaded', () => {
            cargarAgenda();
        });

        // Cargar datos de la agenda
        async function cargarAgenda() {
            try {
                const response = await fetch(`${API_BASE}/agendas/${AGENDA_ID}`);
                if (!response.ok) throw new Error('Agenda no encontrada');

                agendaData = await response.json();

                // Ocultar loading y mostrar formulario
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('agendaForm').classList.remove('hidden');

                // Llenar el formulario con los datos
                llenarFormulario(agendaData);

                // Inicializar autocompletado
                initMemberAutocomplete();
                initHimnoListeners();
                initTipoAgendaListener();

            } catch (error) {
                document.getElementById('loadingIndicator').innerHTML = `
                    <div class="text-red-600">
                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="font-medium">Error al cargar la agenda</p>
                        <p class="text-sm mt-2">${error.message}</p>
                        <a href="/agenda/historial" class="inline-block mt-4 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Volver al historial</a>
                    </div>
                `;
            }
        }

        // Llenar formulario con datos existentes
        function llenarFormulario(agenda) {
            // Fecha
            document.getElementById('fechaInput').value = agenda.fecha;

            // Tipo de agenda
            const tipoSelect = document.getElementById('tipoAgendaSelect');
            tipoSelect.value = agenda.tipo_agenda_id || '';
            esAyuno = agenda.tipo_agenda_id == 1;
            actualizarModoAyuno(esAyuno);

            // Anuncios
            document.querySelector('textarea[name="anuncios"]').value = agenda.anuncios || '';

            // Miembros de liderazgo
            setMemberField('presidido_por_id', agenda.presidido, agenda.presidido_por_id);
            setMemberField('dirigido_por_id', agenda.dirigido, agenda.dirigido_por_id);
            setMemberField('director_himno_id', agenda.director_himno, agenda.director_himno_id);
            setMemberField('pianista_id', agenda.pianista, agenda.pianista_id);

            // Himnos
            if (agenda.himnos && agenda.himnos.length > 0) {
                agenda.himnos.forEach(himno => {
                    const numInput = document.querySelector(`[data-himno-num="${himno.tipo}"]`);
                    const tituloInput = document.querySelector(`[data-himno-titulo="${himno.tipo}"]`);
                    if (numInput) numInput.value = himno.numero;
                    if (tituloInput) tituloInput.value = himno.titulo;
                });
            }

            // Oraciones
            if (agenda.oraciones && agenda.oraciones.length > 0) {
                agenda.oraciones.forEach(oracion => {
                    if (oracion.tipo === 'primera') {
                        setMemberField('oracion_primera_id', oracion.miembro, oracion.miembro_id);
                    } else if (oracion.tipo === 'ultima') {
                        setMemberField('oracion_ultima_id', oracion.miembro, oracion.miembro_id);
                    }
                });
            }

            // Discursos/Mensajes
            if (agenda.mensajes && agenda.mensajes.length > 0) {
                agenda.mensajes.forEach((mensaje, index) => {
                    agregarDiscurso(mensaje.discursante, mensaje.discursante_id, mensaje.tema);
                });
            } else {
                // Si no hay discursos, agregar dos vacios
                agregarDiscurso();
                agregarDiscurso();
            }

            // Testimonios
            if (agenda.testimonios) {
                const testimonios = agenda.testimonios.split('\n').filter(t => t.trim());
                testimonios.forEach(testimonio => {
                    agregarTestimonio(testimonio);
                });
            }

            // Asuntos
            if (agenda.asuntos && agenda.asuntos.length > 0) {
                agenda.asuntos.forEach(asunto => {
                    const tipo = asunto.tipo_asunto || 'relevos';
                    agregarAsunto(tipo, asunto.descripcion);
                });
            }
        }

        // Helper para establecer campo de miembro
        function setMemberField(targetName, member, memberId) {
            const hiddenInput = document.querySelector(`input[name="${targetName}"]`);
            const textInput = document.querySelector(`[data-target="${targetName}"]`);

            if (hiddenInput && memberId) {
                hiddenInput.value = memberId;
            }
            if (textInput && member) {
                textInput.value = member.nombre_completo;
            }
        }

        // Listener para cambio de tipo de agenda
        function initTipoAgendaListener() {
            const tipoSelect = document.getElementById('tipoAgendaSelect');
            tipoSelect.addEventListener('change', function() {
                actualizarModoAyuno(this.value === '1');
            });
        }

        // Actualizar UI segun modo ayuno
        function actualizarModoAyuno(ayuno) {
            esAyuno = ayuno;
            const seccionDiscursos = document.getElementById('seccionDiscursos');
            const seccionTestimonios = document.getElementById('seccionTestimonios');
            const himnoIntermedio = document.getElementById('himnoIntermedioContainer');
            const banner = document.getElementById('tipoReunionBanner');

            if (ayuno) {
                seccionDiscursos.classList.add('hidden');
                seccionTestimonios.classList.remove('hidden');
                himnoIntermedio.classList.add('hidden');
                banner.classList.remove('hidden');
            } else {
                seccionDiscursos.classList.remove('hidden');
                seccionTestimonios.classList.add('hidden');
                himnoIntermedio.classList.remove('hidden');
                banner.classList.add('hidden');
            }
        }

        // Agregar discurso
        function agregarDiscurso(member = null, memberId = null, tema = '') {
            discursoCount++;
            const container = document.getElementById('discursosContainer');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-start bg-gray-50 p-3 rounded-lg';
            div.id = `discurso-${discursoCount}`;
            div.innerHTML = `
                <span class="w-6 h-6 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center text-xs font-medium mt-2">${discursoCount}</span>
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="relative">
                        <input type="text" data-member-input data-target="discurso_${discursoCount}_id" placeholder="Discursante..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" autocomplete="off"
                            value="${member ? member.nombre_completo : ''}">
                        <input type="hidden" name="discurso_${discursoCount}_id" value="${memberId || ''}">
                    </div>
                    <input type="text" name="discurso_${discursoCount}_tema" placeholder="Tema del discurso..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" value="${tema}">
                </div>
                <button type="button" onclick="eliminarDiscurso(${discursoCount})" class="p-2 text-red-400 hover:text-red-600 mt-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            container.appendChild(div);
            initMemberAutocomplete();
        }

        function eliminarDiscurso(id) {
            document.getElementById(`discurso-${id}`)?.remove();
            renumerarDiscursos();
        }

        function renumerarDiscursos() {
            const container = document.getElementById('discursosContainer');
            Array.from(container.children).forEach((d, i) => {
                const badge = d.querySelector('span');
                if (badge) badge.textContent = i + 1;
            });
        }

        // Agregar testimonio
        function agregarTestimonio(nombre = '') {
            testimonioCount++;
            const container = document.getElementById('testimoniosContainer');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center';
            div.id = `testimonio-${testimonioCount}`;
            div.innerHTML = `
                <span class="w-5 h-5 bg-amber-200 text-amber-700 rounded-full flex items-center justify-center text-xs">${testimonioCount}</span>
                <input type="text" data-testimonio placeholder="Nombre del miembro..."
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm" value="${nombre}">
                <button type="button" onclick="eliminarTestimonio(${testimonioCount})" class="p-1 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            container.appendChild(div);
        }

        function eliminarTestimonio(id) {
            document.getElementById(`testimonio-${id}`)?.remove();
            renumerarTestimonios();
        }

        function renumerarTestimonios() {
            const container = document.getElementById('testimoniosContainer');
            Array.from(container.children).forEach((t, i) => {
                const badge = t.querySelector('span');
                if (badge) badge.textContent = i + 1;
            });
        }

        // Recopilar testimonios como texto
        function recopilarTestimonios() {
            const inputs = document.querySelectorAll('[data-testimonio]');
            const nombres = [];
            inputs.forEach(input => {
                if (input.value.trim()) {
                    nombres.push(input.value.trim());
                }
            });
            return nombres.join('\n');
        }

        // Agregar asunto por tipo
        function agregarAsunto(tipo, descripcion = '') {
            asuntoCounters[tipo]++;
            const container = document.getElementById(`${tipo}Container`);
            const count = asuntoCounters[tipo];
            const div = document.createElement('div');
            div.className = 'flex gap-1 items-center';
            div.id = `${tipo}-${count}`;
            div.innerHTML = `
                <input type="text" name="${tipo}_${count}" placeholder="Nombre / Llamamiento..."
                    class="flex-1 px-2 py-1.5 border border-gray-300 rounded text-xs" value="${descripcion}">
                <button type="button" onclick="eliminarAsunto('${tipo}', ${count})" class="p-1 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            container.appendChild(div);
        }

        function eliminarAsunto(tipo, id) {
            document.getElementById(`${tipo}-${id}`)?.remove();
        }

        // Autocompletado de miembros
        function initMemberAutocomplete() {
            document.querySelectorAll('[data-member-input]').forEach(input => {
                if (input.dataset.initialized) return;
                input.dataset.initialized = 'true';

                input.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const value = this.value.trim();
                    const targetName = this.dataset.target;

                    closeAllLists();
                    if (value.length < 2) return;

                    debounceTimer = setTimeout(() => {
                        fetch(`${API_BASE}/miembros?nombre=${encodeURIComponent(value)}`)
                            .then(r => r.json())
                            .then(data => {
                                const members = data.data || data;
                                if (!members.length) return;

                                const list = document.createElement('div');
                                list.className = 'autocomplete-items';

                                members.forEach(m => {
                                    const item = document.createElement('div');
                                    item.textContent = m.nombre_completo;
                                    item.addEventListener('click', () => {
                                        input.value = m.nombre_completo;
                                        document.querySelector(`input[name="${targetName}"]`).value = m.id;
                                        closeAllLists();
                                    });
                                    list.appendChild(item);
                                });

                                input.parentElement.appendChild(list);
                            });
                    }, 300);
                });

                input.addEventListener('keydown', function(e) {
                    let list = this.parentElement.querySelector('.autocomplete-items');
                    if (!list) return;
                    let items = list.getElementsByTagName('div');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        navigateList(items, 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        navigateList(items, -1);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const active = list.querySelector('.autocomplete-active');
                        if (active) active.click();
                    }
                });
            });
        }

        let currentFocus = -1;
        function navigateList(items, direction) {
            currentFocus += direction;
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = items.length - 1;
            Array.from(items).forEach((item, i) => {
                item.classList.toggle('autocomplete-active', i === currentFocus);
            });
        }

        function closeAllLists() {
            document.querySelectorAll('.autocomplete-items').forEach(el => el.remove());
            currentFocus = -1;
        }

        document.addEventListener('click', e => {
            if (!e.target.matches('[data-member-input]')) closeAllLists();
        });

        // Busqueda de himnos
        function initHimnoListeners() {
            document.querySelectorAll('[data-himno-num]').forEach(input => {
                input.addEventListener('change', function() {
                    const tipo = this.dataset.himnoNum;
                    const numero = this.value;
                    const tituloInput = document.querySelector(`[data-himno-titulo="${tipo}"]`);

                    if (!numero) {
                        tituloInput.value = '';
                        return;
                    }

                    fetch(`${API_BASE}/himnos/${numero}`)
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(himno => {
                            tituloInput.value = himno.titulo || himno.data?.titulo || `Himno ${numero}`;
                        })
                        .catch(() => {
                            tituloInput.value = `Himno ${numero}`;
                        });
                });
            });
        }

        // Enviar formulario (actualizar)
        document.getElementById('agendaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            try {
                const formData = new FormData(this);
                const data = {
                    fecha: formData.get('fecha'),
                    tipo_agenda_id: formData.get('tipo_agenda_id') || null,
                    presidido_por_id: formData.get('presidido_por_id') || null,
                    dirigido_por_id: formData.get('dirigido_por_id') || null,
                    pianista_id: formData.get('pianista_id') || null,
                    director_himno_id: formData.get('director_himno_id'),
                    anuncios: formData.get('anuncios') || null,
                    testimonios: esAyuno ? recopilarTestimonios() : null,
                    himnos: [],
                    oraciones: [],
                    mensajes: [],
                    asuntos: []
                };

                // Recopilar himnos
                ['apertura', 'sacramental', 'intermedio', 'cierre'].forEach(tipo => {
                    const num = formData.get(`himno_${tipo}_num`);
                    const titulo = formData.get(`himno_${tipo}_titulo`) || `Himno ${num}`;
                    if (num && (tipo !== 'intermedio' || !esAyuno)) {
                        data.himnos.push({ numero: parseInt(num), titulo, tipo });
                    }
                });

                // Recopilar oraciones
                const oracionPrimeraId = formData.get('oracion_primera_id');
                const oracionUltimaId = formData.get('oracion_ultima_id');
                if (oracionPrimeraId) data.oraciones.push({ tipo: 'primera', miembro_id: parseInt(oracionPrimeraId) });
                if (oracionUltimaId) data.oraciones.push({ tipo: 'ultima', miembro_id: parseInt(oracionUltimaId) });

                // Recopilar discursos (solo si no es ayuno)
                if (!esAyuno) {
                    for (let [key, value] of formData.entries()) {
                        if (key.startsWith('discurso_') && key.endsWith('_id') && value) {
                            const num = key.match(/discurso_(\d+)_id/)[1];
                            const tema = formData.get(`discurso_${num}_tema`) || 'Sin tema';
                            data.mensajes.push({ tema, discursante_id: parseInt(value) });
                        }
                    }
                }

                // Recopilar asuntos por tipo
                ['relevos', 'sostenimientos', 'presentaciones'].forEach(tipo => {
                    for (let [key, value] of formData.entries()) {
                        if (key.startsWith(`${tipo}_`) && value.trim()) {
                            data.asuntos.push({
                                descripcion: value.trim(),
                                tipo_asunto: tipo
                            });
                        }
                    }
                });

                const response = await fetch(`${API_BASE}/agendas/${AGENDA_ID}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Error al actualizar');
                }

                document.getElementById('modalExito').classList.remove('hidden');
                document.getElementById('modalExito').classList.add('flex');

            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Actualizar Agenda';
            }
        });

        // Eliminar agenda
        function eliminarAgenda() {
            document.getElementById('modalEliminar').classList.remove('hidden');
            document.getElementById('modalEliminar').classList.add('flex');
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').classList.add('hidden');
            document.getElementById('modalEliminar').classList.remove('flex');
        }

        async function confirmarEliminar() {
            try {
                const response = await fetch(`${API_BASE}/agendas/${AGENDA_ID}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Error al eliminar');
                }

                window.location.href = '/agenda/historial';

            } catch (error) {
                alert('Error: ' + error.message);
                cerrarModalEliminar();
            }
        }
    </script>
</body>
</html>
