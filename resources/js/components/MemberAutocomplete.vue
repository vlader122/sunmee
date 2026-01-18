<template>
    <div class="member-autocomplete">
        <AutoComplete
            v-model="selectedMember"
            :suggestions="suggestions"
            @complete="searchMembers"
            @item-select="onSelect"
            @clear="onClear"
            optionLabel="nombre_completo"
            :placeholder="placeholder"
            :loading="loading"
            :minLength="2"
            :delay="300"
            :pt="{
                root: { class: 'w-full' },
                pcInputText: { root: { class: 'w-full' } }
            }"
        >
            <template #option="{ option }">
                <div class="flex justify-between items-center w-full py-1" :class="getRowClass(option)">
                    <span class="truncate">{{ option.nombre_completo }}</span>
                    <div class="flex items-center gap-1 ml-2">
                        <Tag
                            v-if="showParticipation && getParticipationLabel(option)"
                            :value="getParticipationLabel(option)"
                            :severity="getParticipationSeverity(option)"
                            class="text-xs"
                        />
                    </div>
                </div>
            </template>
            <template #empty>
                <div class="p-2 text-gray-500 text-sm">No se encontraron miembros</div>
            </template>
        </AutoComplete>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useApi } from '@/composables/useApi';

const props = defineProps({
    modelValue: {
        type: [Number, String, null],
        default: null
    },
    initialMember: {
        type: Object,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Buscar miembro...'
    },
    showParticipation: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'member-selected']);

const { get, loading } = useApi();
const suggestions = ref([]);
const selectedMember = ref(props.initialMember);

// Sincronizar con initialMember
watch(() => props.initialMember, (newMember) => {
    if (newMember) {
        selectedMember.value = newMember;
    }
}, { immediate: true });

// Buscar miembros
async function searchMembers(event) {
    const query = event.query;
    if (query.length < 2) {
        suggestions.value = [];
        return;
    }

    try {
        const params = new URLSearchParams({
            nombre: query,
            participaciones: props.showParticipation ? '1' : '0'
        });
        const data = await get(`/api/miembros?${params}`);
        suggestions.value = Array.isArray(data) ? data : (data.data || []);
    } catch (error) {
        console.error('Error buscando miembros:', error);
        suggestions.value = [];
    }
}

// Seleccionar miembro
function onSelect(event) {
    const member = event.value;
    emit('update:modelValue', member.id);
    emit('member-selected', member);
}

// Limpiar seleccion
function onClear() {
    emit('update:modelValue', null);
    emit('member-selected', null);
}

// Obtener etiqueta de participacion
function getParticipationLabel(member) {
    const p = member.participaciones;
    if (!p) return null;

    const parts = [];
    if (p.discursos_3m > 0) parts.push(`${p.discursos_3m}D`);
    if (p.oraciones_3m > 0) parts.push(`${p.oraciones_3m}O`);

    return parts.length > 0 ? parts.join(' ') : null;
}

// Obtener severidad para el Tag
function getParticipationSeverity(member) {
    const p = member.participaciones;
    if (!p) return 'secondary';

    const total = (p.discursos_3m || 0) + (p.oraciones_3m || 0);
    if (total >= 2) return 'danger';
    if (total === 1) return 'warn';
    return 'secondary';
}

// Obtener clase de fila segun participacion
function getRowClass(member) {
    const p = member.participaciones;
    if (!p) return '';

    const total = (p.discursos_3m || 0) + (p.oraciones_3m || 0);
    if (total >= 2) return 'bg-red-50';
    if (total === 1) return 'bg-amber-50';
    return '';
}
</script>

<style scoped>
.member-autocomplete :deep(.p-autocomplete) {
    width: 100%;
}

.member-autocomplete :deep(.p-autocomplete-input) {
    width: 100%;
}
</style>
