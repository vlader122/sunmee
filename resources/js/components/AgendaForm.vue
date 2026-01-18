<template>
    <AppLayout :pageTitle="mode === 'edit' ? 'Editar Agenda' : 'Crear Agenda'">
        <!-- Loading -->
        <div v-if="loadingData" class="flex items-center justify-center py-16">
            <div class="text-center">
                <ProgressSpinner style="width: 50px; height: 50px" />
                <p class="text-gray-500 mt-4">Cargando agenda...</p>
            </div>
        </div>

        <!-- Contenido principal -->
        <div v-else class="agenda-container">
            <!-- Header con fecha -->
            <div class="agenda-header" :class="headerClass">
                <div class="header-content">
                    <div class="header-icon">
                        <i :class="headerIcon"></i>
                    </div>
                    <div class="header-info">
                        <h2 class="header-title">{{ fechaFormateada }}</h2>
                        <div class="header-subtitle">
                            <Tag :value="tipoReunionLabel" :severity="tipoReunionSeverity" />
                            <span v-if="mode === 'edit'" class="edit-badge">
                                <i class="pi pi-pencil"></i> Editando
                            </span>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <Button
                        icon="pi pi-calendar"
                        severity="secondary"
                        text
                        rounded
                        @click="showDatePicker = true"
                        v-tooltip.bottom="'Cambiar fecha'"
                    />
                    <Button
                        icon="pi pi-cog"
                        severity="secondary"
                        text
                        rounded
                        @click="showSettings = !showSettings"
                        v-tooltip.bottom="'Configuracion'"
                    />
                </div>
            </div>

            <!-- Settings Panel (colapsable) -->
            <transition name="slide">
                <div v-if="showSettings" class="settings-panel">
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label>Fecha del Domingo</label>
                            <DatePicker
                                v-model="agenda.fecha"
                                dateFormat="dd/mm/yy"
                                showIcon
                                class="w-full"
                                @date-select="verificarFecha"
                            />
                        </div>
                        <div class="setting-item">
                            <label>Tipo de Reunion</label>
                            <Select
                                v-model="agenda.tipo_agenda_id"
                                :options="tiposAgenda"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Seleccionar tipo"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Leyenda de participaciones -->
            <div class="participation-legend">
                <span class="legend-item">
                    <span class="legend-dot bg-green-400"></span>
                    <span>Disponible</span>
                </span>
                <span class="legend-item">
                    <span class="legend-dot bg-amber-400"></span>
                    <span>1 participacion</span>
                </span>
                <span class="legend-item">
                    <span class="legend-dot bg-red-400"></span>
                    <span>2+ participaciones</span>
                </span>
                <span class="legend-item text-gray-400">
                    <i class="pi pi-info-circle"></i>
                    Ultimos 3 meses
                </span>
            </div>

            <!-- Layout principal -->
            <div class="main-layout">
                <!-- Formulario -->
                <div class="form-container">
                    <form @submit.prevent="guardarAgenda">
                        <!-- Seccion: Liderazgo -->
                        <section class="form-section">
                            <div class="section-header">
                                <div class="section-icon blue">
                                    <i class="pi pi-users"></i>
                                </div>
                                <div>
                                    <h3>Presidencia y Direccion</h3>
                                    <p>Quienes estaran al frente de la reunion</p>
                                </div>
                            </div>
                            <div class="section-content">
                                <div class="form-grid-2">
                                    <div class="form-field">
                                        <label><i class="pi pi-star-fill text-amber-400"></i> Preside</label>
                                        <MemberAutocomplete
                                            v-model="agenda.presidido_por_id"
                                            :initial-member="agenda.presidido"
                                            placeholder="Buscar miembro..."
                                        />
                                    </div>
                                    <div class="form-field">
                                        <label><i class="pi pi-megaphone text-blue-400"></i> Dirige</label>
                                        <MemberAutocomplete
                                            v-model="agenda.dirigido_por_id"
                                            :initial-member="agenda.dirigido"
                                            placeholder="Buscar miembro..."
                                        />
                                    </div>
                                    <div class="form-field">
                                        <label><i class="pi pi-volume-up text-green-400"></i> Director de Musica</label>
                                        <MemberAutocomplete
                                            v-model="agenda.director_himno_id"
                                            :initial-member="agenda.director_himno"
                                            placeholder="Buscar miembro..."
                                        />
                                    </div>
                                    <div class="form-field">
                                        <label><i class="pi pi-th-large text-purple-400"></i> Pianista</label>
                                        <MemberAutocomplete
                                            v-model="agenda.pianista_id"
                                            :initial-member="agenda.pianista"
                                            placeholder="Buscar miembro..."
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Himnos -->
                        <section class="form-section">
                            <div class="section-header">
                                <div class="section-icon green">
                                    <i class="pi pi-book"></i>
                                </div>
                                <div>
                                    <h3>Himnos</h3>
                                    <p>Selecciona los himnos de la reunion</p>
                                </div>
                            </div>
                            <div class="section-content">
                                <div class="hymns-grid">
                                    <div
                                        v-for="tipo in himnosTipos"
                                        :key="tipo.value"
                                        class="hymn-card"
                                        :class="{ 'hidden': tipo.value === 'intermedio' && esAyuno }"
                                    >
                                        <div class="hymn-label">{{ tipo.label }}</div>
                                        <div class="hymn-inputs">
                                            <InputNumber
                                                v-model="himnos[tipo.value].numero"
                                                :min="1"
                                                :max="341"
                                                placeholder="#"
                                                class="hymn-number"
                                                @update:modelValue="buscarHimno(tipo.value)"
                                            />
                                            <div class="hymn-title">
                                                {{ himnos[tipo.value].titulo || 'Sin seleccionar' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Oraciones -->
                        <section class="form-section">
                            <div class="section-header">
                                <div class="section-icon purple">
                                    <i class="pi pi-heart"></i>
                                </div>
                                <div>
                                    <h3>Oraciones</h3>
                                    <p>Asigna quienes haran las oraciones</p>
                                </div>
                            </div>
                            <div class="section-content">
                                <div class="prayers-grid">
                                    <div class="prayer-card">
                                        <div class="prayer-icon first">
                                            <i class="pi pi-sun"></i>
                                        </div>
                                        <div class="prayer-content">
                                            <label>Primera Oracion</label>
                                            <MemberAutocomplete
                                                v-model="oraciones.primera.miembro_id"
                                                :initial-member="oraciones.primera.miembro"
                                                placeholder="Buscar miembro..."
                                            />
                                        </div>
                                    </div>
                                    <div class="prayer-card">
                                        <div class="prayer-icon last">
                                            <i class="pi pi-moon"></i>
                                        </div>
                                        <div class="prayer-content">
                                            <label>Ultima Oracion</label>
                                            <MemberAutocomplete
                                                v-model="oraciones.ultima.miembro_id"
                                                :initial-member="oraciones.ultima.miembro"
                                                placeholder="Buscar miembro..."
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Discursos (solo reunion regular) -->
                        <section v-if="!esAyuno" class="form-section">
                            <div class="section-header">
                                <div class="section-icon orange">
                                    <i class="pi pi-microphone"></i>
                                </div>
                                <div>
                                    <h3>Discursos</h3>
                                    <p>{{ discursos.length }} discurso{{ discursos.length !== 1 ? 's' : '' }} programado{{ discursos.length !== 1 ? 's' : '' }}</p>
                                </div>
                                <Button
                                    icon="pi pi-plus"
                                    label="Agregar"
                                    severity="success"
                                    size="small"
                                    outlined
                                    class="ml-auto"
                                    @click="agregarDiscurso"
                                />
                            </div>
                            <div class="section-content">
                                <div class="speeches-list">
                                    <TransitionGroup name="list">
                                        <div
                                            v-for="(discurso, index) in discursos"
                                            :key="index"
                                            class="speech-card"
                                        >
                                            <div class="speech-number">{{ index + 1 }}</div>
                                            <div class="speech-content">
                                                <div class="speech-field">
                                                    <div class="discursante-header">
                                                        <label>Discursante</label>
                                                        <div class="discursante-toggle">
                                                            <button
                                                                type="button"
                                                                :class="['toggle-btn', { active: !discurso.esInvitado }]"
                                                                @click="discurso.esInvitado = false; discurso.discursante_nombre = ''"
                                                            >
                                                                <i class="pi pi-user"></i>
                                                                Miembro
                                                            </button>
                                                            <button
                                                                type="button"
                                                                :class="['toggle-btn', { active: discurso.esInvitado }]"
                                                                @click="discurso.esInvitado = true; discurso.discursante_id = null; discurso.discursante = null"
                                                            >
                                                                <i class="pi pi-user-plus"></i>
                                                                Invitado
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <MemberAutocomplete
                                                        v-if="!discurso.esInvitado"
                                                        v-model="discurso.discursante_id"
                                                        :initial-member="discurso.discursante"
                                                        placeholder="Buscar miembro..."
                                                    />
                                                    <InputText
                                                        v-else
                                                        v-model="discurso.discursante_nombre"
                                                        placeholder="Nombre del invitado o misionero..."
                                                        class="w-full invitado-input"
                                                    />
                                                </div>
                                                <div class="speech-field">
                                                    <label>Tema</label>
                                                    <InputText
                                                        v-model="discurso.tema"
                                                        placeholder="Tema del discurso..."
                                                        class="w-full"
                                                    />
                                                </div>
                                            </div>
                                            <Button
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                rounded
                                                @click="eliminarDiscurso(index)"
                                                v-tooltip.left="'Eliminar'"
                                            />
                                        </div>
                                    </TransitionGroup>
                                    <div v-if="discursos.length === 0" class="empty-speeches">
                                        <i class="pi pi-microphone"></i>
                                        <p>No hay discursos programados</p>
                                        <Button label="Agregar primer discurso" icon="pi pi-plus" @click="agregarDiscurso" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Testimonios (solo ayuno) -->
                        <section v-if="esAyuno" class="form-section testimonies-section">
                            <div class="section-header">
                                <div class="section-icon amber">
                                    <i class="pi pi-comments"></i>
                                </div>
                                <div>
                                    <h3>Testimonios</h3>
                                    <p>Registro de testimonios compartidos</p>
                                </div>
                                <Button
                                    icon="pi pi-plus"
                                    label="Agregar"
                                    severity="warn"
                                    size="small"
                                    outlined
                                    class="ml-auto"
                                    @click="agregarTestimonio"
                                />
                            </div>
                            <div class="section-content">
                                <div class="testimonies-list">
                                    <TransitionGroup name="list">
                                        <div
                                            v-for="(testimonio, index) in testimonios"
                                            :key="index"
                                            class="testimony-item"
                                        >
                                            <span class="testimony-number">{{ index + 1 }}</span>
                                            <InputText
                                                v-model="testimonios[index]"
                                                placeholder="Nombre de quien compartio..."
                                                class="flex-1"
                                            />
                                            <Button
                                                icon="pi pi-times"
                                                severity="danger"
                                                text
                                                rounded
                                                size="small"
                                                @click="eliminarTestimonio(index)"
                                            />
                                        </div>
                                    </TransitionGroup>
                                    <div v-if="testimonios.length === 0" class="empty-testimonies">
                                        <i class="pi pi-comments"></i>
                                        <p>Agrega los testimonios despues de la reunion</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Asuntos del Barrio -->
                        <section class="form-section">
                            <div class="section-header">
                                <div class="section-icon yellow">
                                    <i class="pi pi-clipboard"></i>
                                </div>
                                <div>
                                    <h3>Asuntos del Barrio</h3>
                                    <p>Relevos, sostenimientos y presentaciones</p>
                                </div>
                            </div>
                            <div class="section-content">
                                <div class="matters-grid">
                                    <div
                                        v-for="tipo in asuntosTipos"
                                        :key="tipo.value"
                                        class="matter-card"
                                        :class="tipo.value"
                                    >
                                        <div class="matter-header">
                                            <i :class="tipo.icon"></i>
                                            <span>{{ tipo.label }}</span>
                                            <Button
                                                icon="pi pi-plus"
                                                text
                                                rounded
                                                size="small"
                                                @click="asuntos[tipo.value].push('')"
                                            />
                                        </div>
                                        <div class="matter-list">
                                            <TransitionGroup name="list">
                                                <div
                                                    v-for="(asunto, index) in asuntos[tipo.value]"
                                                    :key="index"
                                                    class="matter-item"
                                                >
                                                    <InputText
                                                        v-model="asuntos[tipo.value][index]"
                                                        placeholder="Descripcion..."
                                                        class="flex-1"
                                                    />
                                                    <Button
                                                        icon="pi pi-times"
                                                        severity="secondary"
                                                        text
                                                        rounded
                                                        size="small"
                                                        @click="asuntos[tipo.value].splice(index, 1)"
                                                    />
                                                </div>
                                            </TransitionGroup>
                                            <p v-if="asuntos[tipo.value].length === 0" class="matter-empty">
                                                Sin {{ tipo.label.toLowerCase() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Seccion: Anuncios -->
                        <section class="form-section">
                            <div class="section-header">
                                <div class="section-icon teal">
                                    <i class="pi pi-megaphone"></i>
                                </div>
                                <div>
                                    <h3>Anuncios</h3>
                                    <p>Informacion importante para la congregacion</p>
                                </div>
                            </div>
                            <div class="section-content">
                                <Textarea
                                    v-model="agenda.anuncios"
                                    rows="4"
                                    placeholder="Escribe los anuncios aqui..."
                                    class="w-full announcements-textarea"
                                />
                            </div>
                        </section>

                        <!-- Botones de accion -->
                        <div class="form-actions">
                            <Button
                                type="submit"
                                :label="mode === 'edit' ? 'Guardar Cambios' : 'Crear Agenda'"
                                :icon="mode === 'edit' ? 'pi pi-save' : 'pi pi-check'"
                                :loading="saving"
                                :severity="mode === 'edit' ? 'warn' : 'success'"
                                size="large"
                                class="action-primary"
                            />
                            <Button
                                v-if="mode === 'edit'"
                                label="Eliminar"
                                icon="pi pi-trash"
                                severity="danger"
                                outlined
                                @click="confirmarEliminar"
                            />
                            <Button
                                :label="mode === 'edit' ? 'Cancelar' : 'Limpiar'"
                                icon="pi pi-refresh"
                                severity="secondary"
                                outlined
                                @click="mode === 'edit' ? (location.href = '/agenda/historial') : limpiarFormulario()"
                            />
                        </div>
                    </form>
                </div>

                <!-- Panel de sugerencias -->
                <aside class="suggestions-aside">
                    <SuggestionsPanel />
                </aside>
            </div>
        </div>

        <!-- Dialogs -->
        <Dialog
            v-model:visible="showDatePicker"
            header="Seleccionar Fecha"
            modal
            :style="{ width: '350px' }"
        >
            <DatePicker
                v-model="agenda.fecha"
                inline
                @date-select="showDatePicker = false; verificarFecha()"
            />
        </Dialog>

        <Dialog
            v-model:visible="showSuccessDialog"
            :header="mode === 'edit' ? 'Cambios Guardados' : 'Agenda Creada'"
            modal
            :closable="false"
            :style="{ width: '400px' }"
        >
            <div class="success-dialog-content">
                <div class="success-icon">
                    <i class="pi pi-check"></i>
                </div>
                <p>{{ mode === 'edit' ? 'Los cambios se guardaron correctamente' : 'La agenda se creo correctamente' }}</p>
            </div>
            <template #footer>
                <div class="success-dialog-actions">
                    <Button
                        label="Ver Historial"
                        icon="pi pi-list"
                        severity="secondary"
                        outlined
                        @click="location.href = '/agenda/historial'"
                    />
                    <Button
                        :label="mode === 'edit' ? 'Seguir Editando' : 'Crear Nueva'"
                        :icon="mode === 'edit' ? 'pi pi-pencil' : 'pi pi-plus'"
                        @click="mode === 'edit' ? (showSuccessDialog = false) : resetForm()"
                    />
                </div>
            </template>
        </Dialog>

        <Toast position="bottom-right" />
        <ConfirmDialog />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { useApi } from '@/composables/useApi';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from './layout/AppLayout.vue';
import MemberAutocomplete from './MemberAutocomplete.vue';
import SuggestionsPanel from './SuggestionsPanel.vue';

const props = defineProps({
    mode: { type: String, default: 'create' },
    agendaId: { type: Number, default: null }
});

const { get, post, put, del } = useApi();
const toast = useToast();
const confirm = useConfirm();

// Estado UI
const loadingData = ref(false);
const saving = ref(false);
const showSuccessDialog = ref(false);
const showDatePicker = ref(false);
const showSettings = ref(false);

// Estado del formulario
const agenda = reactive({
    fecha: null,
    tipo_agenda_id: null,
    anuncios: '',
    presidido_por_id: null,
    dirigido_por_id: null,
    director_himno_id: null,
    pianista_id: null,
    presidido: null,
    dirigido: null,
    director_himno: null,
    pianista: null
});

const himnos = reactive({
    apertura: { numero: null, titulo: '' },
    sacramental: { numero: null, titulo: '' },
    intermedio: { numero: null, titulo: '' },
    cierre: { numero: null, titulo: '' }
});

const oraciones = reactive({
    primera: { miembro_id: null, miembro: null },
    ultima: { miembro_id: null, miembro: null }
});

const discursos = ref([
    { discursante_id: null, discursante: null, discursante_nombre: '', esInvitado: false, tema: '' },
    { discursante_id: null, discursante: null, discursante_nombre: '', esInvitado: false, tema: '' }
]);

const testimonios = ref([]);

const asuntos = reactive({
    relevos: [],
    sostenimientos: [],
    presentaciones: []
});

// Opciones
const tiposAgenda = [
    { label: 'Regular', value: null },
    { label: 'Ayuno y Testimonios', value: 1 },
    { label: 'Conferencia de Estaca', value: 2 },
    { label: 'Especial', value: 3 }
];

const himnosTipos = [
    { label: 'Apertura', value: 'apertura' },
    { label: 'Sacramental', value: 'sacramental' },
    { label: 'Intermedio', value: 'intermedio' },
    { label: 'Cierre', value: 'cierre' }
];

const asuntosTipos = [
    { label: 'Relevos', value: 'relevos', icon: 'pi pi-user-minus' },
    { label: 'Sostenimientos', value: 'sostenimientos', icon: 'pi pi-user-plus' },
    { label: 'Presentaciones', value: 'presentaciones', icon: 'pi pi-id-card' }
];

// Computed
const esAyuno = computed(() => agenda.tipo_agenda_id === 1);

const fechaFormateada = computed(() => {
    if (!agenda.fecha) return 'Selecciona una fecha';
    const date = new Date(agenda.fecha);
    return date.toLocaleDateString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

const tipoReunionLabel = computed(() => {
    const tipo = tiposAgenda.find(t => t.value === agenda.tipo_agenda_id);
    return tipo ? tipo.label : 'Regular';
});

const tipoReunionSeverity = computed(() => {
    switch (agenda.tipo_agenda_id) {
        case 1: return 'warn';
        case 2: return 'info';
        case 3: return 'secondary';
        default: return 'success';
    }
});

const headerClass = computed(() => ({
    'header-ayuno': esAyuno.value,
    'header-edit': props.mode === 'edit',
    'header-create': props.mode === 'create' && !esAyuno.value
}));

const headerIcon = computed(() => {
    if (esAyuno.value) return 'pi pi-comments';
    return props.mode === 'edit' ? 'pi pi-pencil' : 'pi pi-calendar-plus';
});

// Metodos
function verificarFecha() {
    if (!agenda.fecha) return;
    const fecha = new Date(agenda.fecha);
    const dia = fecha.getDate();
    if (dia <= 7) {
        agenda.tipo_agenda_id = 1;
    }
}

async function buscarHimno(tipo) {
    const numero = himnos[tipo].numero;
    if (!numero) {
        himnos[tipo].titulo = '';
        return;
    }
    try {
        const data = await get(`/api/himnos/${numero}`);
        himnos[tipo].titulo = data.titulo || `Himno ${numero}`;
    } catch {
        himnos[tipo].titulo = `Himno ${numero}`;
    }
}

function agregarDiscurso() {
    discursos.value.push({ discursante_id: null, discursante: null, discursante_nombre: '', esInvitado: false, tema: '' });
}

function eliminarDiscurso(index) {
    discursos.value.splice(index, 1);
}

function agregarTestimonio() {
    testimonios.value.push('');
}

function eliminarTestimonio(index) {
    testimonios.value.splice(index, 1);
}

async function guardarAgenda() {
    saving.value = true;

    const data = {
        fecha: formatDate(agenda.fecha),
        tipo_agenda_id: agenda.tipo_agenda_id,
        anuncios: agenda.anuncios,
        presidido_por_id: agenda.presidido_por_id,
        dirigido_por_id: agenda.dirigido_por_id,
        director_himno_id: agenda.director_himno_id,
        pianista_id: agenda.pianista_id,
        testimonios: esAyuno.value ? testimonios.value.filter(t => t.trim()).join('\n') : null,
        himnos: Object.entries(himnos)
            .filter(([tipo, h]) => h.numero && (tipo !== 'intermedio' || !esAyuno.value))
            .map(([tipo, h]) => ({ tipo, numero: h.numero, titulo: h.titulo })),
        oraciones: [
            oraciones.primera.miembro_id && { tipo: 'primera', miembro_id: oraciones.primera.miembro_id },
            oraciones.ultima.miembro_id && { tipo: 'ultima', miembro_id: oraciones.ultima.miembro_id }
        ].filter(Boolean),
        mensajes: esAyuno.value ? [] : discursos.value
            .filter(d => d.discursante_id || d.discursante_nombre?.trim())
            .map(d => ({
                discursante_id: d.esInvitado ? null : d.discursante_id,
                discursante_nombre: d.esInvitado ? d.discursante_nombre.trim() : null,
                tema: d.tema || 'Sin tema'
            })),
        asuntos: Object.entries(asuntos)
            .flatMap(([tipo, items]) => items.filter(i => i.trim()).map(descripcion => ({ tipo_asunto: tipo, descripcion })))
    };

    try {
        if (props.mode === 'edit') {
            await put(`/api/agendas/${props.agendaId}`, data);
        } else {
            await post('/api/agendas', data);
        }
        showSuccessDialog.value = true;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 5000 });
    } finally {
        saving.value = false;
    }
}

function confirmarEliminar() {
    confirm.require({
        message: 'Esta accion no se puede deshacer. La agenda sera eliminada permanentemente.',
        header: 'Eliminar Agenda',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await del(`/api/agendas/${props.agendaId}`);
                location.href = '/agenda/historial';
            } catch (error) {
                toast.add({ severity: 'error', summary: 'Error', detail: error.message, life: 5000 });
            }
        }
    });
}

function resetForm() {
    showSuccessDialog.value = false;
    agenda.fecha = getProximoDomingo();
    agenda.tipo_agenda_id = null;
    agenda.anuncios = '';
    agenda.presidido_por_id = null;
    agenda.dirigido_por_id = null;
    agenda.director_himno_id = null;
    agenda.pianista_id = null;
    agenda.presidido = null;
    agenda.dirigido = null;
    agenda.director_himno = null;
    agenda.pianista = null;

    Object.keys(himnos).forEach(k => { himnos[k].numero = null; himnos[k].titulo = ''; });
    oraciones.primera = { miembro_id: null, miembro: null };
    oraciones.ultima = { miembro_id: null, miembro: null };
    discursos.value = [
        { discursante_id: null, discursante: null, discursante_nombre: '', esInvitado: false, tema: '' },
        { discursante_id: null, discursante: null, discursante_nombre: '', esInvitado: false, tema: '' }
    ];
    testimonios.value = [];
    asuntos.relevos = [];
    asuntos.sostenimientos = [];
    asuntos.presentaciones = [];

    verificarFecha();
}

function limpiarFormulario() {
    resetForm();
}

function getProximoDomingo() {
    const hoy = new Date();
    const dias = (7 - hoy.getDay()) % 7 || 7;
    const domingo = new Date(hoy);
    domingo.setDate(hoy.getDate() + dias);
    return domingo;
}

function formatDate(date) {
    if (!date) return null;
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}

async function cargarAgenda() {
    if (props.mode !== 'edit' || !props.agendaId) return;

    loadingData.value = true;
    try {
        const data = await get(`/api/agendas/${props.agendaId}`);

        agenda.fecha = data.fecha ? new Date(data.fecha) : null;
        agenda.tipo_agenda_id = data.tipo_agenda_id;
        agenda.anuncios = data.anuncios || '';
        agenda.presidido_por_id = data.presidido_por_id;
        agenda.dirigido_por_id = data.dirigido_por_id;
        agenda.director_himno_id = data.director_himno_id;
        agenda.pianista_id = data.pianista_id;
        agenda.presidido = data.presidido;
        agenda.dirigido = data.dirigido;
        agenda.director_himno = data.director_himno;
        agenda.pianista = data.pianista;

        if (data.himnos) {
            data.himnos.forEach(h => {
                if (himnos[h.tipo]) {
                    himnos[h.tipo].numero = h.numero;
                    himnos[h.tipo].titulo = h.titulo;
                }
            });
        }

        if (data.oraciones) {
            data.oraciones.forEach(o => {
                if (o.tipo === 'primera') {
                    oraciones.primera = { miembro_id: o.miembro_id, miembro: o.miembro };
                } else if (o.tipo === 'ultima') {
                    oraciones.ultima = { miembro_id: o.miembro_id, miembro: o.miembro };
                }
            });
        }

        if (data.mensajes?.length) {
            discursos.value = data.mensajes.map(m => ({
                discursante_id: m.discursante_id,
                discursante: m.discursante,
                discursante_nombre: m.discursante_nombre || '',
                esInvitado: !m.discursante_id && !!m.discursante_nombre,
                tema: m.tema
            }));
        }

        if (data.testimonios) {
            testimonios.value = data.testimonios.split('\n').filter(t => t.trim());
        }

        if (data.asuntos) {
            data.asuntos.forEach(a => {
                const tipo = a.tipo_asunto || 'relevos';
                if (asuntos[tipo]) {
                    asuntos[tipo].push(a.descripcion);
                }
            });
        }
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la agenda', life: 5000 });
    } finally {
        loadingData.value = false;
    }
}

onMounted(() => {
    if (props.mode === 'create') {
        agenda.fecha = getProximoDomingo();
        verificarFecha();
    } else {
        cargarAgenda();
    }
});
</script>

<style scoped>
/* Container */
.agenda-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Header */
.agenda-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.agenda-header.header-ayuno {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.agenda-header.header-edit {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.header-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    text-transform: capitalize;
}

.header-subtitle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.25rem;
}

.edit-badge {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    opacity: 0.9;
}

.header-actions {
    display: flex;
    gap: 0.25rem;
}

.header-actions .p-button {
    color: white !important;
}

/* Settings Panel */
.settings-panel {
    background: white;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.setting-item label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

/* Participation Legend */
.participation-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 1rem;
    font-size: 0.75rem;
    color: #64748b;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

/* Main Layout */
.main-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
}

@media (max-width: 1200px) {
    .main-layout {
        grid-template-columns: 1fr;
    }
    .suggestions-aside {
        display: none;
    }
}

/* Form Container */
.form-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Form Sections */
.form-section {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.section-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.section-header p {
    margin: 0;
    font-size: 0.75rem;
    color: #64748b;
}

.section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.section-icon.blue { background: #dbeafe; color: #2563eb; }
.section-icon.green { background: #dcfce7; color: #16a34a; }
.section-icon.purple { background: #f3e8ff; color: #9333ea; }
.section-icon.orange { background: #ffedd5; color: #ea580c; }
.section-icon.amber { background: #fef3c7; color: #d97706; }
.section-icon.yellow { background: #fef9c3; color: #ca8a04; }
.section-icon.teal { background: #ccfbf1; color: #0d9488; }

.section-content {
    padding: 1.25rem;
}

/* Form Grid */
.form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
}

.form-field label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

/* Hymns Grid */
.hymns-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .hymns-grid {
        grid-template-columns: 1fr;
    }
}

.hymn-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.875rem;
}

.hymn-card.hidden {
    display: none;
}

.hymn-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.hymn-inputs {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.hymn-number {
    width: 70px;
}

.hymn-title {
    flex: 1;
    font-size: 0.875rem;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Prayers Grid */
.prayers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .prayers-grid {
        grid-template-columns: 1fr;
    }
}

.prayer-card {
    display: flex;
    gap: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem;
}

.prayer-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.prayer-icon.first {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
}

.prayer-icon.last {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4f46e5;
}

.prayer-content {
    flex: 1;
}

.prayer-content label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0.5rem;
}

/* Speeches List */
.speeches-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.speech-card {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #e2e8f0;
}

.speech-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.speech-content {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

@media (max-width: 640px) {
    .speech-content {
        grid-template-columns: 1fr;
    }
}

.speech-field label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.25rem;
}

/* Discursante Toggle */
.discursante-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.discursante-header label {
    margin-bottom: 0;
}

.discursante-toggle {
    display: flex;
    background: #e2e8f0;
    border-radius: 6px;
    padding: 2px;
}

.discursante-toggle .toggle-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border: none;
    background: transparent;
    border-radius: 4px;
    font-size: 0.7rem;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.discursante-toggle .toggle-btn i {
    font-size: 0.7rem;
}

.discursante-toggle .toggle-btn:hover {
    color: #475569;
}

.discursante-toggle .toggle-btn.active {
    background: white;
    color: #f97316;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.invitado-input {
    border-color: #fed7aa !important;
    background: #fffbeb !important;
}

.invitado-input:focus {
    border-color: #f97316 !important;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2) !important;
}

.empty-speeches {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}

.empty-speeches i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.empty-speeches p {
    margin-bottom: 1rem;
}

/* Testimonies */
.testimonies-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.testimony-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.testimony-number {
    width: 24px;
    height: 24px;
    background: #fef3c7;
    color: #d97706;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.empty-testimonies {
    text-align: center;
    padding: 1.5rem;
    color: #94a3b8;
}

.empty-testimonies i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

/* Matters Grid */
.matters-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 900px) {
    .matters-grid {
        grid-template-columns: 1fr;
    }
}

.matter-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.875rem;
    border: 1px solid #e2e8f0;
}

.matter-card.relevos { border-left: 3px solid #ef4444; }
.matter-card.sostenimientos { border-left: 3px solid #22c55e; }
.matter-card.presentaciones { border-left: 3px solid #3b82f6; }

.matter-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
}

.matter-header .p-button {
    margin-left: auto;
}

.matter-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.matter-item {
    display: flex;
    gap: 0.5rem;
}

.matter-empty {
    font-size: 0.75rem;
    color: #94a3b8;
    text-align: center;
    padding: 0.5rem;
}

/* Announcements */
.announcements-textarea {
    border-radius: 10px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.action-primary {
    flex: 1;
}

@media (max-width: 640px) {
    .form-actions {
        flex-direction: column;
    }
    .action-primary {
        order: -1;
    }
}

/* Suggestions Aside */
.suggestions-aside {
    position: sticky;
    top: 80px;
    height: fit-content;
}

/* Success Dialog */
.success-dialog-content {
    text-align: center;
    padding: 1rem 0;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.success-icon i {
    font-size: 2.5rem;
    color: white;
}

.success-dialog-content p {
    color: #64748b;
    margin: 0;
}

.success-dialog-actions {
    display: flex;
    gap: 0.75rem;
    width: 100%;
}

.success-dialog-actions .p-button {
    flex: 1;
}

/* Transitions */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
