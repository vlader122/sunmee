<template>
    <AppLayout pageTitle="Historial de Agendas">
        <!-- Header con estadisticas -->
        <div class="history-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="pi pi-calendar"></i>
                </div>
                <div class="header-info">
                    <h2>Historial de Agendas</h2>
                    <p>{{ totalRecords }} agendas registradas</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-value">{{ proximasCount }}</span>
                    <span class="stat-label">Proximas</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ ayunoCount }}</span>
                    <span class="stat-label">Ayuno</span>
                </div>
            </div>
            <a href="/agenda/crear" class="header-action">
                <Button icon="pi pi-plus" label="Nueva Agenda" />
            </a>
        </div>

        <!-- Filtros -->
        <div class="filters-bar">
            <div class="search-box">
                <i class="pi pi-search"></i>
                <InputText
                    v-model="searchQuery"
                    placeholder="Buscar por fecha o participante..."
                    class="search-input"
                />
            </div>
            <div class="filter-chips">
                <Chip
                    v-for="filter in filters"
                    :key="filter.value"
                    :label="filter.label"
                    :class="{ 'active': activeFilter === filter.value }"
                    @click="activeFilter = filter.value"
                />
            </div>
            <Select
                v-model="sortOrder"
                :options="sortOptions"
                optionLabel="label"
                optionValue="value"
                class="sort-select"
            />
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-container">
            <ProgressSpinner style="width: 50px; height: 50px" />
            <p>Cargando agendas...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredAgendas.length === 0" class="empty-state">
            <div class="empty-icon">
                <i class="pi pi-calendar-times"></i>
            </div>
            <h3>No hay agendas</h3>
            <p v-if="searchQuery || activeFilter !== 'all'">No se encontraron resultados con los filtros actuales</p>
            <p v-else>Crea tu primera agenda sacramental</p>
            <a v-if="!searchQuery && activeFilter === 'all'" href="/agenda/crear">
                <Button label="Crear Primera Agenda" icon="pi pi-plus" />
            </a>
            <Button
                v-else
                label="Limpiar Filtros"
                icon="pi pi-filter-slash"
                severity="secondary"
                @click="clearFilters"
            />
        </div>

        <!-- Lista de Agendas -->
        <div v-else class="agendas-grid">
            <TransitionGroup name="list">
                <div
                    v-for="agenda in filteredAgendas"
                    :key="agenda.id"
                    class="agenda-card"
                    :class="getAgendaClass(agenda)"
                >
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="date-badge" :class="getStatusClass(agenda)">
                            <span class="day">{{ getDay(agenda.fecha) }}</span>
                            <span class="month">{{ getMonth(agenda.fecha) }}</span>
                        </div>
                        <div class="card-title">
                            <h3>{{ formatDateShort(agenda.fecha) }}</h3>
                            <div class="card-tags">
                                <Tag
                                    :value="getStatusLabel(agenda)"
                                    :severity="getStatusSeverity(agenda)"
                                    class="status-tag"
                                />
                                <Tag
                                    v-if="agenda.tipo_agenda_id"
                                    :value="getTipoAgenda(agenda.tipo_agenda_id)"
                                    :severity="agenda.tipo_agenda_id === 1 ? 'warn' : 'info'"
                                />
                            </div>
                        </div>
                        <div class="card-actions">
                            <Button
                                icon="pi pi-ellipsis-v"
                                severity="secondary"
                                text
                                rounded
                                @click="toggleMenu($event, agenda)"
                            />
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="info-grid">
                            <div v-if="agenda.presidido" class="info-item">
                                <i class="pi pi-star-fill text-amber-400"></i>
                                <span>{{ agenda.presidido.nombre_completo }}</span>
                            </div>
                            <div v-if="agenda.director_himno" class="info-item">
                                <i class="pi pi-volume-up text-green-500"></i>
                                <span>{{ agenda.director_himno.nombre_completo }}</span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="card-stats">
                            <div v-if="agenda.himnos?.length" class="stat-badge green">
                                <i class="pi pi-book"></i>
                                <span>{{ agenda.himnos.length }}</span>
                            </div>
                            <div v-if="agenda.oraciones?.length" class="stat-badge purple">
                                <i class="pi pi-heart"></i>
                                <span>{{ agenda.oraciones.length }}</span>
                            </div>
                            <div v-if="agenda.mensajes?.length && agenda.tipo_agenda_id !== 1" class="stat-badge orange">
                                <i class="pi pi-microphone"></i>
                                <span>{{ agenda.mensajes.length }}</span>
                            </div>
                            <div v-if="agenda.tipo_agenda_id === 1 && agenda.testimonios" class="stat-badge amber">
                                <i class="pi pi-comments"></i>
                                <span>{{ getTestimoniosCount(agenda) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer">
                        <Button
                            label="Ver Detalle"
                            icon="pi pi-eye"
                            severity="info"
                            text
                            size="small"
                            @click="verDetalle(agenda)"
                        />
                        <div class="footer-actions">
                            <a :href="`/agenda/editar/${agenda.id}`">
                                <Button icon="pi pi-pencil" severity="secondary" text rounded size="small" v-tooltip.top="'Editar'" />
                            </a>
                            <a :href="`/agenda/pdf/${agenda.id}`" target="_blank">
                                <Button icon="pi pi-file-pdf" severity="danger" text rounded size="small" v-tooltip.top="'PDF'" />
                            </a>
                            <Button
                                icon="pi pi-whatsapp"
                                severity="success"
                                text
                                rounded
                                size="small"
                                @click="enviarNotificaciones(agenda)"
                                :loading="sendingWhatsApp === agenda.id"
                                v-tooltip.top="'WhatsApp'"
                            />
                        </div>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Paginacion -->
        <div v-if="totalRecords > perPage" class="pagination-container">
            <Paginator
                :rows="perPage"
                :totalRecords="totalRecords"
                :first="(currentPage - 1) * perPage"
                @page="onPageChange"
                template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
            />
        </div>

        <!-- Context Menu -->
        <Menu ref="menuRef" :model="menuItems" popup />

        <!-- Modal Detalle -->
        <Dialog
            v-model:visible="showDetailDialog"
            modal
            :style="{ width: '700px' }"
            :pt="{ root: { class: 'detail-dialog' } }"
        >
            <template #header>
                <div class="dialog-header">
                    <div class="dialog-date" :class="selectedAgenda ? getStatusClass(selectedAgenda) : ''">
                        <i class="pi pi-calendar"></i>
                    </div>
                    <div>
                        <h3>{{ selectedAgenda ? formatDateShort(selectedAgenda.fecha) : '' }}</h3>
                        <div class="dialog-tags">
                            <Tag
                                v-if="selectedAgenda"
                                :value="getStatusLabel(selectedAgenda)"
                                :severity="getStatusSeverity(selectedAgenda)"
                            />
                            <Tag
                                v-if="selectedAgenda?.tipo_agenda_id"
                                :value="getTipoAgenda(selectedAgenda.tipo_agenda_id)"
                                :severity="selectedAgenda.tipo_agenda_id === 1 ? 'warn' : 'info'"
                            />
                        </div>
                    </div>
                </div>
            </template>

            <div v-if="selectedAgenda" class="detail-content">
                <!-- Liderazgo -->
                <section class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-users"></i>
                        <span>Liderazgo</span>
                    </div>
                    <div class="leadership-grid">
                        <div v-if="selectedAgenda.presidido" class="leader-card">
                            <div class="leader-icon amber"><i class="pi pi-star-fill"></i></div>
                            <div class="leader-info">
                                <span class="leader-role">Preside</span>
                                <span class="leader-name">{{ selectedAgenda.presidido.nombre_completo }}</span>
                            </div>
                        </div>
                        <div v-if="selectedAgenda.dirigido" class="leader-card">
                            <div class="leader-icon blue"><i class="pi pi-megaphone"></i></div>
                            <div class="leader-info">
                                <span class="leader-role">Dirige</span>
                                <span class="leader-name">{{ selectedAgenda.dirigido.nombre_completo }}</span>
                            </div>
                        </div>
                        <div v-if="selectedAgenda.director_himno" class="leader-card">
                            <div class="leader-icon green"><i class="pi pi-volume-up"></i></div>
                            <div class="leader-info">
                                <span class="leader-role">Director</span>
                                <span class="leader-name">{{ selectedAgenda.director_himno.nombre_completo }}</span>
                            </div>
                        </div>
                        <div v-if="selectedAgenda.pianista" class="leader-card">
                            <div class="leader-icon purple"><i class="pi pi-th-large"></i></div>
                            <div class="leader-info">
                                <span class="leader-role">Pianista</span>
                                <span class="leader-name">{{ selectedAgenda.pianista.nombre_completo }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Himnos -->
                <section v-if="selectedAgenda.himnos?.length" class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-book"></i>
                        <span>Himnos</span>
                    </div>
                    <div class="hymns-list">
                        <div v-for="himno in selectedAgenda.himnos" :key="himno.id" class="hymn-item">
                            <span class="hymn-type">{{ himno.tipo }}</span>
                            <span class="hymn-number">#{{ himno.numero }}</span>
                            <span class="hymn-title">{{ himno.titulo }}</span>
                        </div>
                    </div>
                </section>

                <!-- Oraciones -->
                <section v-if="selectedAgenda.oraciones?.length" class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-heart"></i>
                        <span>Oraciones</span>
                    </div>
                    <div class="prayers-list">
                        <div v-for="oracion in selectedAgenda.oraciones" :key="oracion.id" class="prayer-item">
                            <div class="prayer-type" :class="oracion.tipo">
                                <i :class="oracion.tipo === 'primera' ? 'pi pi-sun' : 'pi pi-moon'"></i>
                            </div>
                            <div class="prayer-info">
                                <span class="prayer-label">{{ oracion.tipo === 'primera' ? 'Primera' : 'Ultima' }} Oracion</span>
                                <span class="prayer-name">{{ oracion.miembro?.nombre_completo || 'Sin asignar' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Discursos -->
                <section v-if="selectedAgenda.mensajes?.length && selectedAgenda.tipo_agenda_id !== 1" class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-microphone"></i>
                        <span>Discursos</span>
                    </div>
                    <div class="speeches-list">
                        <div v-for="(mensaje, idx) in selectedAgenda.mensajes" :key="mensaje.id" class="speech-item">
                            <span class="speech-number">{{ idx + 1 }}</span>
                            <div class="speech-info">
                                <span class="speech-name">
                                    {{ mensaje.discursante?.nombre_completo || mensaje.discursante_nombre || 'Sin asignar' }}
                                    <Tag v-if="mensaje.discursante_nombre && !mensaje.discursante_id" value="Invitado" severity="warn" style="font-size: 0.6rem; padding: 0.1rem 0.3rem; margin-left: 0.5rem;" />
                                </span>
                                <span class="speech-theme">{{ mensaje.tema || 'Sin tema' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Testimonios -->
                <section v-if="selectedAgenda.testimonios && selectedAgenda.tipo_agenda_id === 1" class="detail-section testimonios">
                    <div class="section-title">
                        <i class="pi pi-comments"></i>
                        <span>Testimonios</span>
                    </div>
                    <div class="testimonios-list">
                        <div v-for="(testimonio, idx) in testimoniosList" :key="idx" class="testimonio-item">
                            <span class="testimonio-number">{{ idx + 1 }}</span>
                            <span class="testimonio-name">{{ testimonio }}</span>
                        </div>
                    </div>
                </section>

                <!-- Asuntos del Barrio -->
                <section v-if="selectedAgenda.asuntos?.length" class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-clipboard"></i>
                        <span>Asuntos del Barrio</span>
                    </div>
                    <div class="matters-grid">
                        <div v-if="relevos.length" class="matter-group relevos">
                            <h5><i class="pi pi-user-minus"></i> Relevos</h5>
                            <ul>
                                <li v-for="(a, i) in relevos" :key="i">{{ a.descripcion }}</li>
                            </ul>
                        </div>
                        <div v-if="sostenimientos.length" class="matter-group sostenimientos">
                            <h5><i class="pi pi-user-plus"></i> Sostenimientos</h5>
                            <ul>
                                <li v-for="(a, i) in sostenimientos" :key="i">{{ a.descripcion }}</li>
                            </ul>
                        </div>
                        <div v-if="presentaciones.length" class="matter-group presentaciones">
                            <h5><i class="pi pi-id-card"></i> Presentaciones</h5>
                            <ul>
                                <li v-for="(a, i) in presentaciones" :key="i">{{ a.descripcion }}</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Anuncios -->
                <section v-if="selectedAgenda.anuncios" class="detail-section">
                    <div class="section-title">
                        <i class="pi pi-megaphone"></i>
                        <span>Anuncios</span>
                    </div>
                    <div class="announcements-box">
                        {{ selectedAgenda.anuncios }}
                    </div>
                </section>
            </div>

            <template #footer>
                <div class="dialog-footer">
                    <a :href="`/agenda/editar/${selectedAgenda?.id}`">
                        <Button label="Editar" icon="pi pi-pencil" severity="warn" outlined />
                    </a>
                    <a :href="`/agenda/pdf/${selectedAgenda?.id}`" target="_blank">
                        <Button label="Descargar PDF" icon="pi pi-file-pdf" severity="danger" outlined />
                    </a>
                    <Button
                        label="Enviar WhatsApp"
                        icon="pi pi-whatsapp"
                        severity="success"
                        @click="selectedAgenda && enviarNotificaciones(selectedAgenda); showDetailDialog = false"
                    />
                </div>
            </template>
        </Dialog>

        <Toast position="bottom-right" />
        <ConfirmDialog />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '@/composables/useApi';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from './layout/AppLayout.vue';
import Menu from 'primevue/menu';

const { get, post, loading } = useApi();
const toast = useToast();
const confirm = useConfirm();

// Estado
const agendas = ref([]);
const currentPage = ref(1);
const perPage = ref(10);
const totalRecords = ref(0);
const showDetailDialog = ref(false);
const selectedAgenda = ref(null);
const sendingWhatsApp = ref(null);
const searchQuery = ref('');
const activeFilter = ref('all');
const sortOrder = ref('desc');
const menuRef = ref(null);
const menuAgenda = ref(null);

// Opciones
const filters = [
    { label: 'Todas', value: 'all' },
    { label: 'Proximas', value: 'upcoming' },
    { label: 'Pasadas', value: 'past' },
    { label: 'Ayuno', value: 'fasting' }
];

const sortOptions = [
    { label: 'Mas recientes', value: 'desc' },
    { label: 'Mas antiguas', value: 'asc' }
];

const menuItems = computed(() => [
    {
        label: 'Ver Detalle',
        icon: 'pi pi-eye',
        command: () => menuAgenda.value && verDetalle(menuAgenda.value)
    },
    {
        label: 'Editar',
        icon: 'pi pi-pencil',
        command: () => menuAgenda.value && (location.href = `/agenda/editar/${menuAgenda.value.id}`)
    },
    {
        label: 'Descargar PDF',
        icon: 'pi pi-file-pdf',
        command: () => menuAgenda.value && window.open(`/agenda/pdf/${menuAgenda.value.id}`, '_blank')
    },
    { separator: true },
    {
        label: 'Enviar WhatsApp',
        icon: 'pi pi-whatsapp',
        command: () => menuAgenda.value && enviarNotificaciones(menuAgenda.value)
    }
]);

// Computed
const filteredAgendas = computed(() => {
    let result = [...agendas.value];
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    // Filtro por tipo
    if (activeFilter.value !== 'all') {
        result = result.filter(a => {
            const fecha = new Date(a.fecha);
            fecha.setHours(0, 0, 0, 0);
            switch (activeFilter.value) {
                case 'upcoming': return fecha >= hoy;
                case 'past': return fecha < hoy;
                case 'fasting': return a.tipo_agenda_id === 1;
                default: return true;
            }
        });
    }

    // Busqueda
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(a =>
            formatDateShort(a.fecha).toLowerCase().includes(query) ||
            a.presidido?.nombre_completo?.toLowerCase().includes(query) ||
            a.director_himno?.nombre_completo?.toLowerCase().includes(query)
        );
    }

    return result;
});

const proximasCount = computed(() => {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    return agendas.value.filter(a => new Date(a.fecha) >= hoy).length;
});

const ayunoCount = computed(() => {
    return agendas.value.filter(a => a.tipo_agenda_id === 1).length;
});

const testimoniosList = computed(() => {
    if (!selectedAgenda.value?.testimonios) return [];
    return selectedAgenda.value.testimonios.split('\n').filter(t => t.trim());
});

const relevos = computed(() => {
    if (!selectedAgenda.value?.asuntos) return [];
    return selectedAgenda.value.asuntos.filter(a => a.tipo_asunto === 'relevos');
});

const sostenimientos = computed(() => {
    if (!selectedAgenda.value?.asuntos) return [];
    return selectedAgenda.value.asuntos.filter(a => a.tipo_asunto === 'sostenimientos');
});

const presentaciones = computed(() => {
    if (!selectedAgenda.value?.asuntos) return [];
    return selectedAgenda.value.asuntos.filter(a => a.tipo_asunto === 'presentaciones');
});

// Metodos
async function cargarAgendas(page = 1) {
    currentPage.value = page;
    try {
        const data = await get(`/api/agendas?page=${page}&per_page=20`);
        agendas.value = data.data || [];
        totalRecords.value = data.total || 0;
        perPage.value = data.per_page || 20;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar las agendas',
            life: 3000
        });
    }
}

function onPageChange(event) {
    const page = Math.floor(event.first / perPage.value) + 1;
    cargarAgendas(page);
}

function clearFilters() {
    searchQuery.value = '';
    activeFilter.value = 'all';
}

function toggleMenu(event, agenda) {
    menuAgenda.value = agenda;
    menuRef.value.toggle(event);
}

function getDay(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.getDate();
}

function getMonth(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', { month: 'short' }).toUpperCase();
}

function formatDateShort(fecha) {
    if (!fecha) return 'Sin fecha';
    let dateStr = fecha;
    if (fecha.includes(' ')) dateStr = fecha.split(' ')[0];
    if (fecha.includes('T')) dateStr = fecha.split('T')[0];
    const date = new Date(dateStr + 'T12:00:00');
    if (isNaN(date.getTime())) return fecha;
    return date.toLocaleDateString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
}

function getTipoAgenda(tipo) {
    const tipos = { 1: 'Ayuno', 2: 'Conf. Estaca', 3: 'Especial' };
    return tipos[tipo] || 'Regular';
}

function getAgendaStatus(agenda) {
    const fecha = new Date(agenda.fecha);
    const hoy = new Date();
    fecha.setHours(0, 0, 0, 0);
    hoy.setHours(0, 0, 0, 0);
    if (fecha.getTime() === hoy.getTime()) return 'today';
    if (fecha > hoy) return 'upcoming';
    return 'past';
}

function getStatusLabel(agenda) {
    const status = getAgendaStatus(agenda);
    return { today: 'Hoy', upcoming: 'Proxima', past: 'Pasada' }[status];
}

function getStatusSeverity(agenda) {
    const status = getAgendaStatus(agenda);
    return { today: 'warn', upcoming: 'success', past: 'secondary' }[status];
}

function getStatusClass(agenda) {
    return getAgendaStatus(agenda);
}

function getAgendaClass(agenda) {
    return {
        'is-fasting': agenda.tipo_agenda_id === 1,
        'is-today': getAgendaStatus(agenda) === 'today',
        'is-upcoming': getAgendaStatus(agenda) === 'upcoming'
    };
}

function getTestimoniosCount(agenda) {
    if (!agenda.testimonios) return 0;
    return agenda.testimonios.split('\n').filter(t => t.trim()).length;
}

async function verDetalle(agenda) {
    try {
        const data = await get(`/api/agendas/${agenda.id}`);
        selectedAgenda.value = data;
        showDetailDialog.value = true;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el detalle',
            life: 3000
        });
    }
}

async function enviarNotificaciones(agenda) {
    confirm.require({
        message: 'Enviar notificaciones por WhatsApp a los discursantes y personas asignadas a las oraciones?',
        header: 'Confirmar Envio',
        icon: 'pi pi-whatsapp',
        acceptLabel: 'Enviar',
        rejectLabel: 'Cancelar',
        accept: async () => {
            sendingWhatsApp.value = agenda.id;
            try {
                const data = await post(`/api/whatsapp/send/${agenda.id}`);
                let message = data.message || 'Notificaciones enviadas';
                if (data.results?.skipped?.length) {
                    message += `\n\nOmitidos: ${data.results.skipped.length}`;
                }
                toast.add({
                    severity: 'success',
                    summary: 'Enviado',
                    detail: message,
                    life: 5000
                });
            } catch (error) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.message || 'No se pudieron enviar las notificaciones',
                    life: 5000
                });
            } finally {
                sendingWhatsApp.value = null;
            }
        }
    });
}

onMounted(() => cargarAgendas());
</script>

<style scoped>
/* Header */
.history-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
    border-radius: 16px;
    margin-bottom: 1rem;
    color: white;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.header-info h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.header-info p {
    margin: 0.25rem 0 0;
    opacity: 0.8;
    font-size: 0.875rem;
}

.header-stats {
    display: flex;
    gap: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.75rem;
    opacity: 0.8;
}

.header-action {
    text-decoration: none;
}

/* Filters */
.filters-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    min-width: 200px;
    background: #f8fafc;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.search-box i {
    color: #94a3b8;
}

.search-input {
    border: none;
    background: transparent;
    flex: 1;
}

.filter-chips {
    display: flex;
    gap: 0.5rem;
}

.filter-chips .p-chip {
    cursor: pointer;
    transition: all 0.2s;
}

.filter-chips .p-chip.active {
    background: #3b82f6;
    color: white;
}

.sort-select {
    width: 160px;
}

/* Loading & Empty */
.loading-container {
    text-align: center;
    padding: 4rem;
    color: #64748b;
}

.loading-container p {
    margin-top: 1rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.empty-icon i {
    font-size: 2.5rem;
    color: #94a3b8;
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    color: #1e293b;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 1.5rem;
}

/* Agendas Grid */
.agendas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1rem;
}

@media (max-width: 480px) {
    .agendas-grid {
        grid-template-columns: 1fr;
    }
}

/* Agenda Card */
.agenda-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.agenda-card:hover {
    box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.agenda-card.is-fasting {
    border-left: 4px solid #f59e0b;
}

.agenda-card.is-today {
    border-left: 4px solid #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, white 100%);
}

.agenda-card.is-upcoming {
    border-left: 4px solid #22c55e;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}

.date-badge {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #64748b;
}

.date-badge.today {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.date-badge.upcoming {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
}

.date-badge.past {
    background: #f1f5f9;
    color: #64748b;
}

.date-badge .day {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1;
}

.date-badge .month {
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
}

.card-title {
    flex: 1;
}

.card-title h3 {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    text-transform: capitalize;
}

.card-tags {
    display: flex;
    gap: 0.375rem;
    margin-top: 0.375rem;
}

.card-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.agenda-card:hover .card-actions {
    opacity: 1;
}

.card-body {
    padding: 1rem 1.25rem;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: #475569;
}

.info-item i {
    font-size: 0.875rem;
}

.card-stats {
    display: flex;
    gap: 0.5rem;
}

.stat-badge {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.stat-badge.green { background: #dcfce7; color: #16a34a; }
.stat-badge.purple { background: #f3e8ff; color: #9333ea; }
.stat-badge.orange { background: #ffedd5; color: #ea580c; }
.stat-badge.amber { background: #fef3c7; color: #d97706; }

.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

.footer-actions {
    display: flex;
    gap: 0.25rem;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

/* Dialog */
.dialog-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.dialog-date {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: #f1f5f9;
    color: #64748b;
}

.dialog-date.today { background: #3b82f6; color: white; }
.dialog-date.upcoming { background: #22c55e; color: white; }

.dialog-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    text-transform: capitalize;
}

.dialog-tags {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.detail-content {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.detail-section {
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.detail-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.section-title i {
    color: #64748b;
}

/* Leadership Grid */
.leadership-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.leader-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 10px;
}

.leader-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.leader-icon.amber { background: #fef3c7; color: #d97706; }
.leader-icon.blue { background: #dbeafe; color: #2563eb; }
.leader-icon.green { background: #dcfce7; color: #16a34a; }
.leader-icon.purple { background: #f3e8ff; color: #9333ea; }

.leader-info {
    display: flex;
    flex-direction: column;
}

.leader-role {
    font-size: 0.6875rem;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 500;
}

.leader-name {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #1e293b;
}

/* Hymns List */
.hymns-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.hymn-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.875rem;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 0.8125rem;
}

.hymn-type {
    text-transform: capitalize;
    color: #64748b;
    min-width: 80px;
}

.hymn-number {
    font-weight: 600;
    color: #16a34a;
}

.hymn-title {
    flex: 1;
    color: #1e293b;
}

/* Prayers */
.prayers-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.prayer-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 10px;
}

.prayer-type {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.prayer-type.primera {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
}

.prayer-type.ultima {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4f46e5;
}

.prayer-info {
    display: flex;
    flex-direction: column;
}

.prayer-label {
    font-size: 0.6875rem;
    color: #64748b;
    text-transform: uppercase;
}

.prayer-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1e293b;
}

/* Speeches */
.speeches-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.speech-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 10px;
}

.speech-number {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.speech-info {
    display: flex;
    flex-direction: column;
}

.speech-name {
    font-weight: 500;
    color: #1e293b;
}

.speech-theme {
    font-size: 0.75rem;
    color: #64748b;
}

/* Testimonios */
.testimonios-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.testimonio-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #fef3c7;
    border-radius: 8px;
    font-size: 0.8125rem;
}

.testimonio-number {
    width: 20px;
    height: 20px;
    background: #fde68a;
    color: #d97706;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 600;
}

.testimonio-name {
    color: #92400e;
}

/* Matters */
.matters-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

@media (max-width: 600px) {
    .matters-grid {
        grid-template-columns: 1fr;
    }
}

.matter-group {
    padding: 0.75rem;
    border-radius: 10px;
    border: 1px solid;
}

.matter-group.relevos { border-color: #fecaca; background: #fef2f2; }
.matter-group.sostenimientos { border-color: #bbf7d0; background: #f0fdf4; }
.matter-group.presentaciones { border-color: #bfdbfe; background: #eff6ff; }

.matter-group h5 {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    margin: 0 0 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.matter-group.relevos h5 { color: #dc2626; }
.matter-group.sostenimientos h5 { color: #16a34a; }
.matter-group.presentaciones h5 { color: #2563eb; }

.matter-group ul {
    margin: 0;
    padding-left: 1rem;
    font-size: 0.75rem;
    color: #374151;
}

.matter-group li {
    margin-bottom: 0.25rem;
}

/* Announcements */
.announcements-box {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 10px;
    font-size: 0.875rem;
    color: #374151;
    white-space: pre-line;
    line-height: 1.5;
}

/* Dialog Footer */
.dialog-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    width: 100%;
}

.dialog-footer a {
    text-decoration: none;
}

/* Transitions */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.list-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Responsive */
@media (max-width: 768px) {
    .history-header {
        flex-direction: column;
        text-align: center;
    }

    .header-content {
        flex-direction: column;
    }

    .header-stats {
        width: 100%;
        justify-content: center;
    }

    .filters-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-chips {
        justify-content: center;
        flex-wrap: wrap;
    }

    .sort-select {
        width: 100%;
    }

    .leadership-grid,
    .prayers-list {
        grid-template-columns: 1fr;
    }
}
</style>
