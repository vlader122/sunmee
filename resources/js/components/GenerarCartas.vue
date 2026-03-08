<template>
    <AppLayout pageTitle="Generar Cartas de Asignación">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="pi pi-envelope text-blue-600"></i>
                    Generar Cartas de Asignación
                </h1>
                <p class="text-gray-600 mt-1">
                    Genere cartas formales para discursos y oraciones. Se imprimen 2 cartas por hoja.
                </p>
            </div>

            <!-- Filtros -->
            <Card class="mb-6">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-filter text-blue-500"></i>
                        Seleccionar Período
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <DatePicker
                                v-model="filtros.desde"
                                dateFormat="dd/mm/yy"
                                placeholder="Seleccione fecha"
                                :maxDate="filtros.hasta || undefined"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <DatePicker
                                v-model="filtros.hasta"
                                dateFormat="dd/mm/yy"
                                placeholder="Seleccione fecha"
                                :minDate="filtros.desde || undefined"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <Button
                                @click="buscarAsignaciones"
                                label="Buscar Asignaciones"
                                icon="pi pi-search"
                                class="w-full"
                                :loading="cargando"
                                :disabled="!filtros.desde || !filtros.hasta"
                            />
                        </div>
                    </div>

                    <!-- Tipos de asignación -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipos de asignación</label>
                        <div class="flex gap-3">
                            <div
                                v-for="tipo in tiposAsignacion"
                                :key="tipo.value"
                                class="flex items-center gap-2"
                            >
                                <Checkbox
                                    v-model="filtros.tipos"
                                    :value="tipo.value"
                                    :inputId="tipo.value"
                                />
                                <label :for="tipo.value" class="text-sm cursor-pointer">{{ tipo.label }}</label>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Resultados -->
            <Card v-if="asignaciones.length > 0" class="mb-6">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-list text-green-500"></i>
                            Asignaciones Encontradas
                            <Tag :value="asignacionesFiltradas.length.toString()" severity="info" />
                        </div>
                        <div class="flex gap-2">
                            <Button
                                @click="seleccionarTodas"
                                label="Seleccionar Todas"
                                icon="pi pi-check-square"
                                text
                                size="small"
                            />
                            <Button
                                @click="deseleccionarTodas"
                                label="Limpiar"
                                icon="pi pi-times"
                                text
                                size="small"
                            />
                        </div>
                    </div>
                </template>
                <template #content>
                    <DataTable
                        v-model:selection="asignacionesSeleccionadas"
                        :value="asignacionesFiltradas"
                        dataKey="id"
                        stripedRows
                        paginator
                        :rows="10"
                        :rowsPerPageOptions="[5, 10, 25, 50]"
                        class="p-datatable-sm"
                    >
                        <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                        <Column field="fecha_formateada" header="Fecha" sortable style="width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.fecha_formateada }}</span>
                            </template>
                        </Column>
                        <Column field="tipo" header="Tipo" sortable style="width: 150px">
                            <template #body="{ data }">
                                <Tag
                                    :value="data.tipo"
                                    :severity="getTipoSeverity(data.tipo)"
                                    class="text-xs"
                                />
                            </template>
                        </Column>
                        <Column field="destinatario" header="Destinatario" sortable>
                            <template #body="{ data }">
                                <span class="font-medium">{{ data.destinatario }}</span>
                            </template>
                        </Column>
                        <Column field="tema" header="Tema / Detalles">
                            <template #body="{ data }">
                                <div v-if="data.tema" class="text-sm">
                                    <span class="italic">"{{ data.tema }}"</span>
                                    <span v-if="data.tiempo" class="text-gray-500 ml-2">({{ data.tiempo }})</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Acciones -->
                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
                        <span class="text-sm text-gray-600 self-center mr-auto">
                            {{ asignacionesSeleccionadas.length }} asignaciones seleccionadas
                        </span>
                        <Button
                            @click="verPreview"
                            label="Previsualizar"
                            icon="pi pi-eye"
                            severity="secondary"
                            :disabled="asignacionesSeleccionadas.length === 0 || generandoPreview"
                            :loading="generandoPreview"
                        />
                        <Button
                            @click="generarPDF"
                            label="Generar PDF"
                            icon="pi pi-file-pdf"
                            severity="primary"
                            :disabled="asignacionesSeleccionadas.length === 0 || generandoPDF"
                            :loading="generandoPDF"
                        />
                    </div>
                </template>
            </Card>

            <!-- Estado vacío -->
            <Card v-else-if="busquedaRealizada && asignaciones.length === 0" class="mb-6">
                <template #content>
                    <div class="text-center py-8 text-gray-500">
                        <i class="pi pi-inbox text-4xl mb-3 block"></i>
                        <p>No se encontraron asignaciones en el período seleccionado.</p>
                    </div>
                </template>
            </Card>

            <!-- Instrucciones iniciales -->
            <Card v-else class="mb-6">
                <template #content>
                    <div class="text-center py-8 text-gray-500">
                        <i class="pi pi-calendar text-4xl mb-3 block"></i>
                        <p>Seleccione un rango de fechas para buscar asignaciones.</p>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Modal de Previsualización -->
        <Dialog
            v-model:visible="previewVisible"
            header="Vista Previa de Cartas"
            :style="{ width: '90vw', maxWidth: '900px' }"
            :modal="true"
            :closable="true"
            class="preview-dialog"
        >
            <div class="preview-container">
                <div v-if="previewLoading" class="text-center py-12">
                    <ProgressSpinner style="width: 50px; height: 50px" />
                    <p class="mt-3 text-gray-600">Generando vista previa...</p>
                </div>
                <div v-else-if="previewUrl" class="preview-iframe-wrapper">
                    <iframe
                        :src="previewUrl"
                        class="preview-iframe"
                        frameborder="0"
                    ></iframe>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <span class="text-sm text-gray-500">
                        Mostrando {{ asignacionesSeleccionadas.length }} cartas
                        ({{ Math.ceil(asignacionesSeleccionadas.length / 2) }} páginas)
                    </span>
                    <div class="flex gap-2">
                        <Button
                            @click="previewVisible = false"
                            label="Cerrar"
                            icon="pi pi-times"
                            text
                        />
                        <Button
                            @click="generarPDF"
                            label="Descargar PDF"
                            icon="pi pi-download"
                            severity="primary"
                        />
                    </div>
                </div>
            </template>
        </Dialog>

        <Toast position="bottom-right" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useToast } from 'primevue/usetoast';
import AppLayout from './layout/AppLayout.vue';

const toast = useToast();

// Filtros
const filtros = ref({
    desde: null,
    hasta: null,
    tipos: ['Discurso', 'Oración de Apertura', 'Oración de Cierre', 'Oración'],
});

const tiposAsignacion = [
    { label: 'Discursos', value: 'Discurso' },
    { label: 'Oraciones de Apertura', value: 'Oración de Apertura' },
    { label: 'Oraciones de Cierre', value: 'Oración de Cierre' },
    { label: 'Otras Oraciones', value: 'Oración' },
];

// Estado
const cargando = ref(false);
const busquedaRealizada = ref(false);
const asignaciones = ref([]);
const asignacionesSeleccionadas = ref([]);
const generandoPreview = ref(false);
const generandoPDF = ref(false);

// Preview
const previewVisible = ref(false);
const previewUrl = ref(null);
const previewLoading = ref(false);

// Computed
const asignacionesFiltradas = computed(() => {
    return asignaciones.value.filter(a => filtros.value.tipos.includes(a.tipo));
});

// Métodos
function getTipoSeverity(tipo) {
    if (tipo === 'Discurso') return 'primary';
    if (tipo.includes('Apertura')) return 'success';
    if (tipo.includes('Cierre')) return 'warn';
    return 'secondary';
}

function formatDateForAPI(date) {
    if (!date) return null;
    const d = new Date(date);
    return d.toISOString().split('T')[0];
}

async function buscarAsignaciones() {
    if (!filtros.value.desde || !filtros.value.hasta) {
        toast.add({
            severity: 'warn',
            summary: 'Fechas requeridas',
            detail: 'Por favor seleccione ambas fechas',
            life: 3000,
        });
        return;
    }

    cargando.value = true;
    busquedaRealizada.value = false;

    try {
        const desde = formatDateForAPI(filtros.value.desde);
        const hasta = formatDateForAPI(filtros.value.hasta);

        const response = await fetch('/api/cartas/asignaciones', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ desde, hasta }),
        });

        if (!response.ok) {
            throw new Error('Error al obtener asignaciones');
        }

        const data = await response.json();
        asignaciones.value = data.asignaciones || [];
        asignacionesSeleccionadas.value = [];
        busquedaRealizada.value = true;

        if (asignaciones.value.length === 0) {
            toast.add({
                severity: 'info',
                summary: 'Sin resultados',
                detail: 'No se encontraron asignaciones en el período seleccionado',
                life: 3000,
            });
        } else {
            toast.add({
                severity: 'success',
                summary: 'Búsqueda exitosa',
                detail: `${data.total} asignaciones encontradas`,
                life: 3000,
            });
        }
    } catch (error) {
        console.error('Error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar las asignaciones',
            life: 5000,
        });
    } finally {
        cargando.value = false;
    }
}

function seleccionarTodas() {
    asignacionesSeleccionadas.value = [...asignacionesFiltradas.value];
}

function deseleccionarTodas() {
    asignacionesSeleccionadas.value = [];
}

async function verPreview() {
    if (asignacionesSeleccionadas.value.length === 0) {
        toast.add({
            severity: 'warn',
            summary: 'Sin selección',
            detail: 'Por favor seleccione al menos una asignación',
            life: 3000,
        });
        return;
    }

    // Limpiar URL anterior
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    previewVisible.value = true;
    previewLoading.value = true;
    generandoPreview.value = true;

    try {
        const response = await fetch('/api/cartas/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                asignaciones: asignacionesSeleccionadas.value,
                preview: true,
            }),
        });

        if (!response.ok) {
            throw new Error('Error al generar preview');
        }

        const blob = await response.blob();
        previewUrl.value = URL.createObjectURL(blob);
    } catch (error) {
        console.error('Error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo generar la vista previa',
            life: 5000,
        });
        previewVisible.value = false;
    } finally {
        previewLoading.value = false;
        generandoPreview.value = false;
    }
}

async function generarPDF() {
    if (asignacionesSeleccionadas.value.length === 0) {
        toast.add({
            severity: 'warn',
            summary: 'Sin selección',
            detail: 'Por favor seleccione al menos una asignación',
            life: 3000,
        });
        return;
    }

    generandoPDF.value = true;

    try {
        const response = await fetch('/api/cartas/generar-pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                asignaciones: asignacionesSeleccionadas.value,
            }),
        });

        if (!response.ok) {
            throw new Error('Error al generar PDF');
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);

        // Descargar el archivo
        const a = document.createElement('a');
        a.href = url;
        const contentDisposition = response.headers.get('content-disposition');
        const filename = contentDisposition?.match(/filename="(.+)"/)?.[1] || 'cartas-asignacion.pdf';
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        URL.revokeObjectURL(url);

        toast.add({
            severity: 'success',
            summary: 'PDF generado',
            detail: 'Las cartas se han descargado correctamente',
            life: 3000,
        });

        // Cerrar modal si está abierto
        previewVisible.value = false;
    } catch (error) {
        console.error('Error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo generar el PDF',
            life: 5000,
        });
    } finally {
        generandoPDF.value = false;
    }
}

// Limpiar URL de preview al destruir componente
onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<style scoped>
:deep(.preview-dialog .p-dialog-content) {
    padding: 0;
    overflow: hidden;
}

.preview-container {
    background: #f5f5f5;
    min-height: 500px;
}

.preview-iframe-wrapper {
    width: 100%;
    height: 70vh;
}

.preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
}
</style>
