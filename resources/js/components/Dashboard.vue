<template>
    <AppLayout pageTitle="Dashboard">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Stats Cards -->
            <div class="stats-card bg-gradient-to-br from-blue-500 to-blue-600">
                <div class="stats-icon">
                    <i class="pi pi-users"></i>
                </div>
                <div class="stats-content">
                    <span class="stats-value">{{ stats.totalMiembros }}</span>
                    <span class="stats-label">Total Miembros</span>
                </div>
            </div>

            <div class="stats-card bg-gradient-to-br from-green-500 to-green-600">
                <div class="stats-icon">
                    <i class="pi pi-phone"></i>
                </div>
                <div class="stats-content">
                    <span class="stats-value">{{ stats.telefonosValidos }}</span>
                    <span class="stats-label">Tel. Validos</span>
                </div>
            </div>

            <div class="stats-card bg-gradient-to-br from-purple-500 to-purple-600">
                <div class="stats-icon">
                    <i class="pi pi-calendar"></i>
                </div>
                <div class="stats-content">
                    <span class="stats-value">{{ stats.totalAgendas }}</span>
                    <span class="stats-label">Agendas</span>
                </div>
            </div>

            <div class="stats-card bg-gradient-to-br from-amber-500 to-amber-600">
                <div class="stats-icon">
                    <i class="pi pi-clock"></i>
                </div>
                <div class="stats-content">
                    <span class="stats-value text-lg">{{ proximaAgendaTexto }}</span>
                    <span class="stats-label">Proxima</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Acciones Rapidas -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-bolt text-amber-500"></i>
                        Acciones Rapidas
                    </div>
                </template>
                <template #content>
                    <div class="space-y-3">
                        <a href="/agenda/crear" class="action-button primary">
                            <i class="pi pi-plus-circle"></i>
                            <span>Crear Nueva Agenda</span>
                            <i class="pi pi-arrow-right ml-auto"></i>
                        </a>
                        <a href="/agenda/historial" class="action-button">
                            <i class="pi pi-history"></i>
                            <span>Ver Historial</span>
                            <i class="pi pi-arrow-right ml-auto"></i>
                        </a>
                        <a href="/miembros" class="action-button">
                            <i class="pi pi-users"></i>
                            <span>Gestionar Miembros</span>
                            <i class="pi pi-arrow-right ml-auto"></i>
                        </a>
                        <a href="/whatsapp/config" class="action-button">
                            <i class="pi pi-whatsapp"></i>
                            <span>Config. WhatsApp</span>
                            <i class="pi pi-arrow-right ml-auto"></i>
                        </a>
                    </div>
                </template>
            </Card>

            <!-- Ultimas Agendas -->
            <Card class="lg:col-span-2">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-blue-500"></i>
                            Ultimas Agendas
                        </div>
                        <a href="/agenda/historial" class="text-sm text-blue-600 hover:underline">Ver todas</a>
                    </div>
                </template>
                <template #content>
                    <div v-if="loadingAgendas" class="text-center py-8">
                        <ProgressSpinner style="width: 40px; height: 40px" />
                    </div>
                    <div v-else-if="ultimasAgendas.length === 0" class="empty-state">
                        <i class="pi pi-calendar-times"></i>
                        <p>No hay agendas creadas</p>
                        <a href="/agenda/crear" class="text-blue-600 hover:underline">Crear primera agenda</a>
                    </div>
                    <div v-else class="agenda-list">
                        <div v-for="agenda in ultimasAgendas" :key="agenda.id" class="agenda-item">
                            <div class="agenda-icon">
                                <i class="pi pi-calendar"></i>
                            </div>
                            <div class="agenda-info">
                                <p class="agenda-date">{{ formatDate(agenda.fecha) }}</p>
                                <p class="agenda-meta">
                                    {{ getTipoAgenda(agenda.tipo_agenda_id) }}
                                    <span v-if="agenda.mensajes?.length"> - {{ agenda.mensajes.length }} discursos</span>
                                </p>
                            </div>
                            <Tag :value="getAgendaStatus(agenda)" :severity="getAgendaStatusSeverity(agenda)" />
                            <a :href="`/agenda/editar/${agenda.id}`" class="agenda-edit">
                                <i class="pi pi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Sugerencias -->
        <Card class="mt-6">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-star text-amber-500"></i>
                    Miembros Sugeridos
                    <Tag value="Disponibles" severity="success" class="ml-2" />
                </div>
            </template>
            <template #subtitle>
                Miembros con telefono valido que no han participado recientemente
            </template>
            <template #content>
                <div v-if="loadingSugerencias" class="text-center py-4">
                    <ProgressSpinner style="width: 30px; height: 30px" />
                </div>
                <div v-else class="member-grid">
                    <div v-for="miembro in sugerencias" :key="miembro.id" class="member-card">
                        <div class="member-avatar">
                            <i class="pi pi-user"></i>
                        </div>
                        <p class="member-name" :title="miembro.nombre_completo">
                            {{ miembro.nombre_completo }}
                        </p>
                        <Tag
                            :value="getParticipacionLabel(miembro)"
                            :severity="getParticipacionSeverity(miembro)"
                        />
                    </div>
                </div>
            </template>
        </Card>

        <Toast position="bottom-right" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import AppLayout from './layout/AppLayout.vue';

const toast = useToast();

const stats = ref({
    totalMiembros: 0,
    telefonosValidos: 0,
    totalAgendas: 0
});
const ultimasAgendas = ref([]);
const sugerencias = ref([]);
const loadingAgendas = ref(true);
const loadingSugerencias = ref(true);
const proximaAgenda = ref(null);

const proximaAgendaTexto = computed(() => {
    if (proximaAgenda.value) {
        const d = new Date(proximaAgenda.value.fecha);
        return `${d.getDate()}/${d.getMonth() + 1}`;
    }
    return getProximoDomingoCorto();
});

async function cargarDatos() {
    try {
        const [miembrosRes, agendasRes] = await Promise.all([
            fetch('/api/miembros/list?per_page=1000').then(r => r.json()).catch(() => ({ data: [] })),
            fetch('/api/agendas?per_page=100').then(r => r.json()).catch(() => ({ data: [], total: 0 }))
        ]);

        const miembros = miembrosRes.data || [];
        stats.value.totalMiembros = miembros.length;
        stats.value.telefonosValidos = miembros.filter(m => validarTelefono(m.telefono)).length;

        // La API devuelve datos paginados { data: [...], total: X }
        const agendas = agendasRes.data || [];
        stats.value.totalAgendas = agendasRes.total || agendas.length;

        ultimasAgendas.value = agendas
            .sort((a, b) => new Date(b.fecha) - new Date(a.fecha))
            .slice(0, 5);

        const hoy = new Date();
        proximaAgenda.value = agendas
            .filter(a => new Date(a.fecha) >= hoy)
            .sort((a, b) => new Date(a.fecha) - new Date(b.fecha))[0];

        loadingAgendas.value = false;
    } catch (error) {
        loadingAgendas.value = false;
    }

    try {
        const sugRes = await fetch('/api/miembros/sugerencias?limite=12').then(r => r.json());
        sugerencias.value = Array.isArray(sugRes) ? sugRes : [];
        loadingSugerencias.value = false;
    } catch {
        loadingSugerencias.value = false;
    }
}

function validarTelefono(phone) {
    if (!phone?.trim()) return false;
    const tel = phone.trim();
    return /^\d{8,}$/.test(tel) && ['6', '7'].includes(tel[0]);
}

function formatDate(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long' });
}

function getProximoDomingoCorto() {
    const hoy = new Date();
    const dias = (7 - hoy.getDay()) % 7 || 7;
    const domingo = new Date(hoy);
    domingo.setDate(hoy.getDate() + dias);
    return `${domingo.getDate()}/${domingo.getMonth() + 1}`;
}

function getTipoAgenda(tipo) {
    return { 1: 'Ayuno', 2: 'Conf. Estaca', 3: 'Especial' }[tipo] || 'Regular';
}

function getAgendaStatus(agenda) {
    const fecha = new Date(agenda.fecha);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    if (fecha < hoy) return 'Pasada';
    if (fecha.toDateString() === hoy.toDateString()) return 'Hoy';
    return 'Proxima';
}

function getAgendaStatusSeverity(agenda) {
    return { 'Pasada': 'secondary', 'Hoy': 'warn', 'Proxima': 'success' }[getAgendaStatus(agenda)];
}

function getParticipacionLabel(m) {
    const total = (m.discursos_3m || 0) + (m.oraciones_3m || 0);
    if (total === 0) return 'Disponible';
    const parts = [];
    if (m.discursos_3m > 0) parts.push(`${m.discursos_3m}D`);
    if (m.oraciones_3m > 0) parts.push(`${m.oraciones_3m}O`);
    return parts.join(' ');
}

function getParticipacionSeverity(m) {
    const total = (m.discursos_3m || 0) + (m.oraciones_3m || 0);
    return total === 0 ? 'success' : total >= 2 ? 'danger' : 'warn';
}

onMounted(cargarDatos);
</script>

<style scoped>
/* Stats Cards */
.stats-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 12px;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stats-content {
    display: flex;
    flex-direction: column;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

/* Action Buttons */
.action-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    color: #475569;
    transition: all 0.2s;
}

.action-button:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateX(4px);
}

.action-button.primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-color: transparent;
    color: white;
}

.action-button.primary:hover {
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Agenda List */
.agenda-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.agenda-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem;
    background: #f8fafc;
    border-radius: 8px;
    transition: background 0.2s;
}

.agenda-item:hover {
    background: #f1f5f9;
}

.agenda-icon {
    width: 40px;
    height: 40px;
    background: #dbeafe;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
}

.agenda-info {
    flex: 1;
}

.agenda-date {
    font-weight: 500;
    color: #1e293b;
    text-transform: capitalize;
}

.agenda-meta {
    font-size: 0.875rem;
    color: #64748b;
}

.agenda-edit {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #64748b;
    transition: all 0.2s;
}

.agenda-edit:hover {
    background: #e2e8f0;
    color: #3b82f6;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #64748b;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

/* Member Grid */
.member-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
}

.member-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    transition: all 0.2s;
}

.member-card:hover {
    background: #eff6ff;
    transform: translateY(-2px);
}

.member-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 0.5rem;
}

.member-name {
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
    color: #1e293b;
    margin-bottom: 0.5rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}
</style>
