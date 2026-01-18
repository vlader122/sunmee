<template>
    <AppLayout pageTitle="Miembros">
        <!-- Header con estadisticas -->
        <div class="members-header mb-6">
            <div class="header-content">
                <div class="header-title">
                    <div class="title-icon">
                        <i class="pi pi-users"></i>
                    </div>
                    <div>
                        <h1>Gestion de Miembros</h1>
                        <p>Administra los miembros del barrio y sus datos de contacto</p>
                    </div>
                </div>
                <Button
                    label="Nuevo Miembro"
                    icon="pi pi-plus"
                    @click="openAddDialog"
                    class="add-button"
                />
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="pi pi-users"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ stats.total }}</span>
                        <span class="stat-label">Total Miembros</span>
                    </div>
                </div>
                <div class="stat-card stat-valid">
                    <div class="stat-icon">
                        <i class="pi pi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ stats.withValidPhone }}</span>
                        <span class="stat-label">Tel. Valido</span>
                    </div>
                </div>
                <div class="stat-card stat-observed">
                    <div class="stat-icon">
                        <i class="pi pi-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ stats.withObservedPhone }}</span>
                        <span class="stat-label">Observados</span>
                    </div>
                </div>
                <div class="stat-card stat-empty">
                    <div class="stat-icon">
                        <i class="pi pi-phone"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ stats.withoutPhone }}</span>
                        <span class="stat-label">Sin Telefono</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de filtros y busqueda -->
        <div class="filters-bar">
            <div class="filters-left">
                <!-- Busqueda -->
                <div class="search-wrapper">
                    <i class="pi pi-search search-icon"></i>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Buscar miembro..."
                        class="search-input"
                    />
                    <button v-if="searchQuery" class="clear-search" @click="searchQuery = ''">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <!-- Filtros de estado -->
                <div class="filter-chips">
                    <button
                        v-for="filter in filterOptions"
                        :key="filter.value"
                        :class="['filter-chip', { active: activeFilter === filter.value }, filter.class]"
                        @click="activeFilter = filter.value"
                    >
                        <i :class="filter.icon"></i>
                        <span>{{ filter.label }}</span>
                        <span class="chip-count">{{ getFilterCount(filter.value) }}</span>
                    </button>
                </div>
            </div>

            <div class="filters-right">
                <!-- Toggle vista -->
                <div class="view-toggle">
                    <button
                        :class="['toggle-btn', { active: viewMode === 'table' }]"
                        @click="viewMode = 'table'"
                        title="Vista tabla"
                    >
                        <i class="pi pi-list"></i>
                    </button>
                    <button
                        :class="['toggle-btn', { active: viewMode === 'cards' }]"
                        @click="viewMode = 'cards'"
                        title="Vista tarjetas"
                    >
                        <i class="pi pi-th-large"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
            <ProgressSpinner style="width: 50px; height: 50px" />
            <p>Cargando miembros...</p>
        </div>

        <!-- Vista Tabla -->
        <div v-else-if="viewMode === 'table'" class="table-container">
            <DataTable
                :value="filteredMembers"
                :paginator="true"
                :rows="20"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                :pt="{
                    root: { class: 'custom-table' },
                    header: { class: 'table-header' },
                    tbody: { class: 'table-body' }
                }"
            >
                <template #empty>
                    <div class="empty-state">
                        <i class="pi pi-users"></i>
                        <h3>No se encontraron miembros</h3>
                        <p>Intenta con otros filtros o agrega un nuevo miembro</p>
                    </div>
                </template>

                <Column header="#" style="width: 60px">
                    <template #body="{ index }">
                        <span class="row-number">{{ index + 1 }}</span>
                    </template>
                </Column>

                <Column field="nombre_completo" header="Nombre" sortable style="min-width: 280px">
                    <template #body="{ data }">
                        <div class="member-name-cell">
                            <div class="member-avatar" :style="{ background: getAvatarColor(data.nombre_completo) }">
                                {{ getInitials(data.nombre_completo) }}
                            </div>
                            <div class="member-info">
                                <span class="name">{{ data.nombre_completo }}</span>
                                <span v-if="data.participaciones" class="participation-info">
                                    <Tag
                                        v-if="data.participaciones.discursos_3m > 0"
                                        :value="`${data.participaciones.discursos_3m}D`"
                                        severity="info"
                                        class="part-tag"
                                    />
                                    <Tag
                                        v-if="data.participaciones.oraciones_3m > 0"
                                        :value="`${data.participaciones.oraciones_3m}O`"
                                        severity="secondary"
                                        class="part-tag"
                                    />
                                </span>
                            </div>
                        </div>
                    </template>
                </Column>

                <Column field="telefono" header="Telefono" sortable style="min-width: 180px">
                    <template #body="{ data }">
                        <div class="phone-display" :class="getPhoneDisplayClass(data.telefono)">
                            <i class="pi pi-phone"></i>
                            <span>{{ data.telefono || 'Sin telefono' }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Estado" style="width: 120px">
                    <template #body="{ data }">
                        <Tag
                            :value="getPhoneStatusLabel(data.telefono)"
                            :severity="getPhoneSeverity(data.telefono)"
                            :icon="getPhoneTagIcon(data.telefono)"
                        />
                    </template>
                </Column>

                <Column header="Acciones" style="width: 120px">
                    <template #body="{ data }">
                        <div class="actions-cell">
                            <Button
                                icon="pi pi-pencil"
                                severity="info"
                                text
                                rounded
                                @click="openEditDialog(data)"
                                v-tooltip.top="'Editar'"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                rounded
                                @click="confirmDelete(data)"
                                v-tooltip.top="'Eliminar'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Vista Tarjetas -->
        <div v-else class="cards-container">
            <TransitionGroup name="card-list" tag="div" class="cards-grid">
                <div
                    v-for="member in paginatedMembers"
                    :key="member.id"
                    class="member-card"
                    :class="getCardClass(member)"
                >
                    <div class="card-header">
                        <div class="card-avatar" :style="{ background: getAvatarColor(member.nombre_completo) }">
                            {{ getInitials(member.nombre_completo) }}
                        </div>
                        <div class="card-status">
                            <Tag
                                :value="getPhoneStatusLabel(member.telefono)"
                                :severity="getPhoneSeverity(member.telefono)"
                                :icon="getPhoneTagIcon(member.telefono)"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <h3 class="card-name">{{ member.nombre_completo }}</h3>

                        <div class="card-phone-display" :class="getPhoneDisplayClass(member.telefono)">
                            <i class="pi pi-phone"></i>
                            <span>{{ member.telefono || 'Sin telefono' }}</span>
                        </div>

                        <div v-if="member.participaciones" class="card-participations">
                            <div class="part-item" v-if="member.participaciones.discursos_3m > 0">
                                <i class="pi pi-megaphone"></i>
                                <span>{{ member.participaciones.discursos_3m }} discursos</span>
                            </div>
                            <div class="part-item" v-if="member.participaciones.oraciones_3m > 0">
                                <i class="pi pi-heart"></i>
                                <span>{{ member.participaciones.oraciones_3m }} oraciones</span>
                            </div>
                            <div class="part-item new" v-if="!member.participaciones.discursos_3m && !member.participaciones.oraciones_3m">
                                <i class="pi pi-star"></i>
                                <span>Sin participaciones recientes</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <Button
                            icon="pi pi-pencil"
                            label="Editar"
                            severity="secondary"
                            text
                            size="small"
                            @click="openEditDialog(member)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            size="small"
                            @click="confirmDelete(member)"
                        />
                    </div>
                </div>
            </TransitionGroup>

            <!-- Paginacion Cards -->
            <div v-if="filteredMembers.length > cardsPerPage" class="cards-pagination">
                <Paginator
                    :rows="cardsPerPage"
                    :totalRecords="filteredMembers.length"
                    :first="cardsFirst"
                    @page="onCardsPageChange"
                    template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                />
            </div>

            <!-- Empty State Cards -->
            <div v-if="!filteredMembers.length" class="empty-state-cards">
                <i class="pi pi-users"></i>
                <h3>No se encontraron miembros</h3>
                <p>Intenta con otros filtros o agrega un nuevo miembro</p>
                <Button
                    label="Agregar Miembro"
                    icon="pi pi-plus"
                    @click="openAddDialog"
                    class="mt-4"
                />
            </div>
        </div>

        <!-- Dialog Agregar/Editar Miembro -->
        <Dialog
            v-model:visible="showMemberDialog"
            :header="editingMember ? 'Editar Miembro' : 'Nuevo Miembro'"
            modal
            :style="{ width: '450px' }"
            :pt="{
                root: { class: 'member-dialog' },
                header: { class: 'dialog-header' },
                content: { class: 'dialog-content' }
            }"
        >
            <div class="dialog-form">
                <!-- Preview Avatar -->
                <div class="form-avatar-preview">
                    <div class="preview-avatar" :style="{ background: getAvatarColor(memberForm.nombre_completo || 'N') }">
                        {{ getInitials(memberForm.nombre_completo || 'Nuevo') }}
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="pi pi-user"></i>
                        Nombre Completo
                    </label>
                    <InputText
                        v-model="memberForm.nombre_completo"
                        placeholder="Ingresa el nombre completo"
                        class="w-full"
                        :class="{ 'p-invalid': formErrors.nombre_completo }"
                    />
                    <small v-if="formErrors.nombre_completo" class="p-error">
                        {{ formErrors.nombre_completo }}
                    </small>
                </div>

                <div class="form-group">
                    <label>
                        <i class="pi pi-phone"></i>
                        Telefono
                    </label>
                    <div class="phone-form-input" :class="getPhoneWrapperClass(memberForm.telefono)">
                        <InputText
                            v-model="memberForm.telefono"
                            placeholder="Numero de celular (8 digitos)"
                            class="w-full"
                        />
                        <div v-if="memberForm.telefono" class="phone-validation-icon" :class="getPhoneStatusClass(memberForm.telefono)">
                            <i :class="getPhoneStatusIcon(memberForm.telefono)"></i>
                        </div>
                    </div>
                    <small class="phone-hint">
                        <i class="pi pi-info-circle"></i>
                        Formato valido: 8 digitos, inicia con 6 o 7
                    </small>
                </div>
            </div>

            <template #footer>
                <div class="dialog-footer">
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        severity="secondary"
                        text
                        @click="closeMemberDialog"
                    />
                    <Button
                        :label="editingMember ? 'Guardar Cambios' : 'Crear Miembro'"
                        :icon="editingMember ? 'pi pi-check' : 'pi pi-plus'"
                        @click="saveMember"
                        :loading="saving"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Confirm Dialog -->
        <ConfirmDialog :pt="{ root: { class: 'confirm-dialog' } }" />

        <!-- Toast -->
        <Toast position="bottom-right" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useApi } from '@/composables/useApi';
import { usePhoneValidation } from '@/composables/usePhoneValidation';
import { useDebounce } from '@/composables/useDebounce';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from './layout/AppLayout.vue';

const { get, post, put, del, loading } = useApi();
const { validate, getStatusSeverity } = usePhoneValidation();
const toast = useToast();
const confirm = useConfirm();

// Estado
const members = ref([]);
const searchQuery = ref('');
const activeFilter = ref('todos');
const viewMode = ref('table');
const showMemberDialog = ref(false);
const editingMember = ref(null);
const saving = ref(false);
const memberForm = ref({ nombre_completo: '', telefono: '' });
const formErrors = ref({});

// Paginacion para cards
const cardsPerPage = 24;
const cardsFirst = ref(0);

// Opciones de filtro
const filterOptions = [
    { label: 'Todos', value: 'todos', icon: 'pi pi-users', class: 'filter-all' },
    { label: 'Validos', value: 'validos', icon: 'pi pi-check-circle', class: 'filter-valid' },
    { label: 'Observados', value: 'observados', icon: 'pi pi-exclamation-circle', class: 'filter-observed' },
    { label: 'Sin Tel', value: 'sin', icon: 'pi pi-phone', class: 'filter-empty' }
];

// Debounce de busqueda
const debouncedSearch = useDebounce(searchQuery, 300);

// Stats computadas
const stats = computed(() => {
    const total = members.value.length;
    let withValidPhone = 0;
    let withObservedPhone = 0;
    let withoutPhone = 0;

    members.value.forEach(m => {
        const status = validate(m.telefono);
        if (status === 'valid') withValidPhone++;
        else if (status === 'observed') withObservedPhone++;
        else withoutPhone++;
    });

    return { total, withValidPhone, withObservedPhone, withoutPhone };
});

// Miembros filtrados
const filteredMembers = computed(() => {
    let result = members.value;

    // Filtrar por busqueda
    if (debouncedSearch.value) {
        const query = debouncedSearch.value.toLowerCase();
        result = result.filter(m =>
            m.nombre_completo.toLowerCase().includes(query)
        );
    }

    // Filtrar por estado de telefono
    if (activeFilter.value !== 'todos') {
        result = result.filter(m => {
            const status = validate(m.telefono);
            switch (activeFilter.value) {
                case 'validos': return status === 'valid';
                case 'observados': return status === 'observed';
                case 'sin': return status === 'empty';
                default: return true;
            }
        });
    }

    return result;
});

// Miembros paginados para cards
const paginatedMembers = computed(() => {
    const start = cardsFirst.value;
    const end = start + cardsPerPage;
    return filteredMembers.value.slice(start, end);
});

// Obtener conteo por filtro
function getFilterCount(filterValue) {
    if (filterValue === 'todos') return members.value.length;

    return members.value.filter(m => {
        const status = validate(m.telefono);
        switch (filterValue) {
            case 'validos': return status === 'valid';
            case 'observados': return status === 'observed';
            case 'sin': return status === 'empty';
            default: return true;
        }
    }).length;
}

// Cargar miembros
async function loadMembers() {
    try {
        const response = await get('/api/miembros/list?per_page=2000');
        members.value = response.data || [];

        // Cargar participaciones en segundo plano
        loadParticipations();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los miembros',
            life: 3000
        });
    }
}

// Cargar participaciones
async function loadParticipations() {
    try {
        // Obtener participaciones de miembros con telefono valido
        const sugerencias = await get('/api/miembros/sugerencias?limite=1000');

        // Mapear participaciones a miembros
        const participacionesMap = new Map();
        sugerencias.forEach(s => {
            participacionesMap.set(s.id, {
                discursos_3m: s.discursos_3m,
                oraciones_3m: s.oraciones_3m,
                ultima_participacion: s.ultima_participacion
            });
        });

        // Actualizar miembros
        members.value = members.value.map(m => ({
            ...m,
            participaciones: participacionesMap.get(m.id) || null
        }));
    } catch (error) {
        console.error('Error loading participations:', error);
    }
}

// Abrir dialog agregar
function openAddDialog() {
    editingMember.value = null;
    memberForm.value = { nombre_completo: '', telefono: '' };
    formErrors.value = {};
    showMemberDialog.value = true;
}

// Abrir dialog editar
function openEditDialog(member) {
    editingMember.value = member;
    memberForm.value = {
        nombre_completo: member.nombre_completo,
        telefono: member.telefono || ''
    };
    formErrors.value = {};
    showMemberDialog.value = true;
}

// Cerrar dialog
function closeMemberDialog() {
    showMemberDialog.value = false;
    editingMember.value = null;
    memberForm.value = { nombre_completo: '', telefono: '' };
    formErrors.value = {};
}

// Guardar miembro
async function saveMember() {
    // Validar
    formErrors.value = {};
    if (!memberForm.value.nombre_completo?.trim()) {
        formErrors.value.nombre_completo = 'El nombre es requerido';
        return;
    }

    saving.value = true;
    try {
        if (editingMember.value) {
            // Actualizar
            const updated = await put(`/api/miembros/${editingMember.value.id}`, memberForm.value);
            const index = members.value.findIndex(m => m.id === editingMember.value.id);
            if (index !== -1) {
                members.value[index] = { ...members.value[index], ...updated };
            }
            toast.add({
                severity: 'success',
                summary: 'Actualizado',
                detail: 'Miembro actualizado correctamente',
                life: 3000
            });
        } else {
            // Crear
            const created = await post('/api/miembros', memberForm.value);
            members.value.unshift(created);
            toast.add({
                severity: 'success',
                summary: 'Creado',
                detail: 'Miembro agregado correctamente',
                life: 3000
            });
        }
        closeMemberDialog();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: editingMember.value ? 'No se pudo actualizar' : 'No se pudo crear',
            life: 3000
        });
    } finally {
        saving.value = false;
    }
}

// Confirmar eliminacion
function confirmDelete(member) {
    confirm.require({
        message: `¿Estas seguro de eliminar a "${member.nombre_completo}"?`,
        header: 'Confirmar Eliminacion',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Eliminar',
        rejectLabel: 'Cancelar',
        accept: () => deleteMember(member)
    });
}

// Eliminar miembro
async function deleteMember(member) {
    try {
        await del(`/api/miembros/${member.id}`);
        members.value = members.value.filter(m => m.id !== member.id);
        toast.add({
            severity: 'success',
            summary: 'Eliminado',
            detail: 'Miembro eliminado correctamente',
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo eliminar',
            life: 3000
        });
    }
}

// Cambio de pagina cards
function onCardsPageChange(event) {
    cardsFirst.value = event.first;
}

// Helpers visuales
function getInitials(name) {
    if (!name) return 'N';
    const parts = name.split(' ').filter(p => p);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

function getAvatarColor(name) {
    if (!name) return '#6b7280';
    const colors = [
        'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
        'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)',
        'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
        'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
        'linear-gradient(135deg, #10b981 0%, #059669 100%)',
        'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
        'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)',
        'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)'
    ];
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
}

function getCardClass(member) {
    const status = validate(member.telefono);
    return {
        'card-valid': status === 'valid',
        'card-observed': status === 'observed',
        'card-empty': status === 'empty'
    };
}

function getPhoneDisplayClass(phone) {
    const status = validate(phone);
    return {
        'phone-display-valid': status === 'valid',
        'phone-display-observed': status === 'observed',
        'phone-display-empty': status === 'empty'
    };
}

function getPhoneWrapperClass(phone) {
    const status = validate(phone);
    return {
        'phone-valid': status === 'valid',
        'phone-observed': status === 'observed',
        'phone-empty': status === 'empty'
    };
}

function getPhoneStatusClass(phone) {
    const status = validate(phone);
    return {
        'status-valid': status === 'valid',
        'status-observed': status === 'observed',
        'status-empty': status === 'empty'
    };
}

function getPhoneStatusIcon(phone) {
    const status = validate(phone);
    switch (status) {
        case 'valid': return 'pi pi-check';
        case 'observed': return 'pi pi-exclamation-triangle';
        case 'empty': return 'pi pi-minus';
        default: return 'pi pi-minus';
    }
}

function getPhoneStatusLabel(phone) {
    const status = validate(phone);
    switch (status) {
        case 'valid': return 'Valido';
        case 'observed': return 'Observado';
        case 'empty': return 'Sin Tel';
        default: return 'Sin Tel';
    }
}

function getPhoneSeverity(phone) {
    return getStatusSeverity(validate(phone));
}

function getPhoneTagIcon(phone) {
    const status = validate(phone);
    switch (status) {
        case 'valid': return 'pi pi-check-circle';
        case 'observed': return 'pi pi-exclamation-circle';
        case 'empty': return 'pi pi-phone';
        default: return 'pi pi-phone';
    }
}

// Reset paginacion cuando cambia filtro
watch([debouncedSearch, activeFilter], () => {
    cardsFirst.value = 0;
});

// Cargar al montar
onMounted(loadMembers);
</script>

<style scoped>
/* Header */
.members-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 1.5rem;
    color: white;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.title-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.header-title h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.header-title p {
    margin: 0.25rem 0 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.add-button {
    background: rgba(255, 255, 255, 0.15) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.add-button:hover {
    background: rgba(255, 255, 255, 0.25) !important;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: transform 0.2s, background 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.15);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.stat-total .stat-icon { background: rgba(99, 102, 241, 0.3); color: #a5b4fc; }
.stat-valid .stat-icon { background: rgba(34, 197, 94, 0.3); color: #86efac; }
.stat-observed .stat-icon { background: rgba(245, 158, 11, 0.3); color: #fcd34d; }
.stat-empty .stat-icon { background: rgba(239, 68, 68, 0.3); color: #fca5a5; }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.75rem;
    opacity: 0.8;
}

/* Filters Bar */
.filters-bar {
    background: white;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.filters-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    flex: 1;
}

.search-wrapper {
    position: relative;
    min-width: 250px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    padding-left: 38px !important;
    padding-right: 32px !important;
    border-radius: 8px !important;
    width: 100%;
}

.clear-search {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.clear-search:hover {
    color: #6b7280;
    background: #f3f4f6;
}

/* Filter Chips */
.filter-chips {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-chip {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.85rem;
    color: #6b7280;
}

.filter-chip:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.filter-chip.active {
    border-color: #3b82f6;
    background: #eff6ff;
    color: #1d4ed8;
}

.filter-chip.active.filter-valid {
    border-color: #22c55e;
    background: #f0fdf4;
    color: #16a34a;
}

.filter-chip.active.filter-observed {
    border-color: #f59e0b;
    background: #fffbeb;
    color: #d97706;
}

.filter-chip.active.filter-empty {
    border-color: #ef4444;
    background: #fef2f2;
    color: #dc2626;
}

.chip-count {
    background: #e5e7eb;
    padding: 0.125rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

.filter-chip.active .chip-count {
    background: currentColor;
    color: white;
}

/* View Toggle */
.view-toggle {
    display: flex;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px;
}

.toggle-btn {
    padding: 0.5rem 0.75rem;
    border: none;
    background: none;
    border-radius: 6px;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s;
}

.toggle-btn:hover {
    color: #374151;
}

.toggle-btn.active {
    background: white;
    color: #3b82f6;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Loading Container */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem;
    color: #6b7280;
}

.loading-container p {
    margin-top: 1rem;
}

/* Table Container */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.row-number {
    color: #9ca3af;
    font-size: 0.85rem;
}

/* Member Name Cell */
.member-name-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.member-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.member-info .name {
    font-weight: 500;
    color: #1f2937;
}

.participation-info {
    display: flex;
    gap: 0.25rem;
}

.part-tag {
    font-size: 0.65rem !important;
    padding: 0.125rem 0.375rem !important;
}

/* Phone Display */
.phone-display {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-size: 0.9rem;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    color: #6b7280;
}

.phone-display i {
    font-size: 0.85rem;
}

.phone-display-valid {
    border-color: #86efac;
    background: #f0fdf4;
    color: #16a34a;
}

.phone-display-observed {
    border-color: #fcd34d;
    background: #fffbeb;
    color: #d97706;
}

.phone-display-empty {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #dc2626;
}

/* Actions Cell */
.actions-cell {
    display: flex;
    gap: 0.25rem;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem;
}

.empty-state p {
    margin: 0;
}

/* Cards Container */
.cards-container {
    min-height: 400px;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

/* Member Card */
.member-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.member-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.member-card.card-valid {
    border-color: #86efac;
}

.member-card.card-observed {
    border-color: #fcd34d;
}

.member-card.card-empty {
    border-color: #fca5a5;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem;
    background: #f8fafc;
}

.card-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.card-body {
    padding: 0 1rem 1rem;
}

.card-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.75rem;
}

.card-phone-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: #6b7280;
}

.card-phone-display i {
    font-size: 0.85rem;
}

.card-phone-display.phone-display-valid {
    border-color: #86efac;
    background: #f0fdf4;
    color: #16a34a;
}

.card-phone-display.phone-display-observed {
    border-color: #fcd34d;
    background: #fffbeb;
    color: #d97706;
}

.card-phone-display.phone-display-empty {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #dc2626;
}

.card-participations {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.part-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #6b7280;
}

.part-item i {
    font-size: 0.75rem;
    color: #3b82f6;
}

.part-item.new {
    color: #22c55e;
}

.part-item.new i {
    color: #22c55e;
}

.card-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
}

/* Cards Pagination */
.cards-pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Empty State Cards */
.empty-state-cards {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    color: #6b7280;
}

.empty-state-cards i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-cards h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem;
}

.empty-state-cards p {
    margin: 0;
}

/* Dialog */
.dialog-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-avatar-preview {
    display: flex;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.preview-avatar {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #374151;
    font-size: 0.9rem;
}

.form-group label i {
    color: #6b7280;
}

.phone-form-input {
    position: relative;
    display: flex;
    align-items: center;
}

.phone-form-input .p-inputtext {
    padding-right: 40px !important;
}

.phone-validation-icon {
    position: absolute;
    right: 12px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.phone-validation-icon.status-valid {
    background: #dcfce7;
    color: #16a34a;
}

.phone-validation-icon.status-observed {
    background: #fef3c7;
    color: #d97706;
}

.phone-validation-icon.status-empty {
    background: #fee2e2;
    color: #dc2626;
}

.phone-hint {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #9ca3af;
    font-size: 0.8rem;
}

.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Card Animation */
.card-list-enter-active,
.card-list-leave-active {
    transition: all 0.3s ease;
}

.card-list-enter-from,
.card-list-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* Responsive */
@media (max-width: 991px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .filters-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .filters-left {
        flex-direction: column;
        align-items: stretch;
    }

    .search-wrapper {
        min-width: 100%;
    }

    .filter-chips {
        justify-content: center;
    }

    .filters-right {
        justify-content: center;
    }
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .header-content {
        flex-direction: column;
        align-items: stretch;
    }

    .add-button {
        width: 100%;
    }

    .cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>
