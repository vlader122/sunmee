<template>
    <div class="w-72 flex-shrink-0">
        <Card class="sticky top-6">
            <template #title>
                <div class="flex items-center gap-2 text-lg">
                    <i class="pi pi-check-circle text-green-500"></i>
                    Sugerencias
                </div>
            </template>
            <template #subtitle>
                <span class="text-xs text-gray-500">Miembros con telefono valido y menos participaciones</span>
            </template>
            <template #content>
                <!-- Loading -->
                <div v-if="loading" class="text-center py-4">
                    <ProgressSpinner style="width: 30px; height: 30px" />
                    <p class="text-xs text-gray-400 mt-2">Cargando...</p>
                </div>

                <!-- Lista de sugerencias -->
                <div v-else class="space-y-1 max-h-[60vh] overflow-y-auto">
                    <div v-if="suggestions.length === 0" class="text-center py-4 text-gray-500 text-sm">
                        No hay sugerencias disponibles
                    </div>

                    <div
                        v-for="member in suggestions"
                        :key="member.id"
                        class="p-2 rounded hover:bg-blue-50 cursor-pointer border border-transparent hover:border-blue-200 transition-colors group"
                        @click="copyMember(member)"
                    >
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700 truncate flex-1 group-hover:text-blue-700">
                                {{ member.nombre_completo }}
                            </span>
                            <Tag
                                :value="getParticipationLabel(member)"
                                :severity="getParticipationSeverity(member)"
                                class="text-xs ml-1"
                            />
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 flex items-center">
                            <i class="pi pi-phone mr-1 text-xs"></i>
                            {{ member.telefono }}
                        </div>
                    </div>
                </div>

                <!-- Boton actualizar -->
                <Button
                    label="Actualizar"
                    icon="pi pi-refresh"
                    text
                    size="small"
                    class="mt-3 w-full"
                    @click="loadSuggestions"
                    :loading="loading"
                />
            </template>
        </Card>

        <!-- Toast para copiar -->
        <Toast position="bottom-right" group="suggestions" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '@/composables/useApi';
import { useToast } from 'primevue/usetoast';

const { get, loading } = useApi();
const toast = useToast();

const suggestions = ref([]);

async function loadSuggestions() {
    try {
        const data = await get('/api/miembros/sugerencias?limite=20');
        suggestions.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Error cargando sugerencias:', error);
        suggestions.value = [];
    }
}

function getParticipationLabel(member) {
    const total = (member.discursos_3m || 0) + (member.oraciones_3m || 0);
    if (total === 0) return 'Nuevo';

    const parts = [];
    if (member.discursos_3m > 0) parts.push(`${member.discursos_3m}D`);
    if (member.oraciones_3m > 0) parts.push(`${member.oraciones_3m}O`);
    return parts.join(' ');
}

function getParticipationSeverity(member) {
    const total = (member.discursos_3m || 0) + (member.oraciones_3m || 0);
    if (total === 0) return 'success';
    if (total >= 2) return 'danger';
    return 'warn';
}

async function copyMember(member) {
    try {
        await navigator.clipboard.writeText(member.nombre_completo);
        toast.add({
            severity: 'info',
            summary: 'Copiado',
            detail: `"${member.nombre_completo}" copiado al portapapeles`,
            life: 2000,
            group: 'suggestions'
        });
    } catch (error) {
        console.error('Error copiando:', error);
    }
}

onMounted(loadSuggestions);
</script>
