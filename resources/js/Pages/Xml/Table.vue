<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted, computed, watch } from "vue";
import { FilterMatchMode } from '@primevue/core/api';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { subDays, startOfMonth, endOfMonth, startOfWeek, endOfWeek, subMonths, startOfYear, endOfYear } from "date-fns";
import { router } from '@inertiajs/vue3';

const confirm = useConfirm();
const toast = useToast();

const selectedProducts = ref([]);

const allColumns = [
    { field: 'acciones', header: 'Acciones', visible: true },
    { field: 'send_status', header: 'Estado', visible: true },
    { field: 'company_name', header: 'Empresa', visible: true },
    { field: 'uuid', header: 'Uuid', visible: false },
    { field: 'emisor_rfc', header: 'Emisor RFC', visible: true },
    { field: 'emisor_name', header: 'Emisor Nombre', visible: true },
    { field: 'trandate', header: 'Fecha de emisión', visible: true },
    { field: 'trandate_cer', header: 'Fecha de certificación', visible: false },
    { field: 'rfc_pac', header: 'PAC que Certificó', visible: false },
    { field: 'total', header: 'Importe', visible: true },
    { field: 'trandate_cancel', header: 'Fecha de cancelación', visible: false },
    { field: 'order_id', header: 'No de Orden', visible: true },
    { field: 'categoria', header: 'Categoría', visible: true },
    { field: 'ubicacion', header: 'Ubicación', visible: true },
    { field: 'departamento', header: 'Departamento', visible: true },
    { field: 'clase', header: 'Clase', visible: true },
    { field: 'notes', header: 'Notas', visible: true },
    { field: 'termino', header: 'Término', visible: true },
    { field: 'importacion', header: 'Importación', visible: true },
    { field: 'articulo', header: 'Artículo', visible: true },
    { field: 'exclusion', header: 'Exclusión', visible: true },
    { field: 'invoice_provider_type', header: 'Proveedor genérico', visible: true },
    { field: 'tipo_operacion', header: 'Tipo de operación', visible: true },
    { field: 'efecto_comprobante', header: 'Tipo', visible: true },
    { field: 'status', header: 'Estado', visible: true },
];

const selectedColumns = ref(allColumns.filter(col => col.visible));

const onToggle = (val) => {
    const selectedFields = val.map(c => c.field);
    selectedColumns.value = allColumns.filter(col => selectedFields.includes(col.field));
};

// Funciones auxiliares
const props = defineProps({
    terms: Array,
    locations: Array,
    exclusions: Array,
    departments: Array,
    classes: Array,
    categories: Array,
    articles: Array,
    accountingLists: Array,
    invoices: Array,
    operationTypes: Array,
    plantas: Array,
    sesion: Object,
});

const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const currencyFormatter = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
});

const downloadFile = (path) => {
    if (!path) {
        alert("No hay archivo disponible");
        return;
    }
    window.location.href = `/spaces/test-download?path=sat_xml/${encodeURIComponent(path)}`;
};

const getStatusIcon = (state) => {
    switch(state) {
        case 'pending': return 'pi pi-exclamation-triangle';
        case 'error': return 'pi pi-times-circle';
        case 'correct': return 'pi pi-check-circle';
        default: return 'pi pi-question';
    }
};

const getStatusColor = (state) => {
    switch(state) {
        case 'pending': return 'text-yellow-500';
        case 'error': return 'text-red-500';
        case 'correct': return 'text-green-500';
        default: return 'text-gray-400';
    }
};

const statusLabels = {
    pending: 'Pendiente',
    error: 'Error',
    correct: 'Correcto',
    default: 'Desconocido',
};

const formatProviderType = (invoice_provider_type) => {
    if (invoice_provider_type == 8345) return "Caja chica";
    if (invoice_provider_type == 8244) return "Varios";
    if (invoice_provider_type == 12185) return "Viaticos";
    return "";
};

const formatType = (efecto_comprobante) => {
    if (efecto_comprobante == 'P') return "Pago";
    if (efecto_comprobante == 'I') return "Ingreso";
    if (efecto_comprobante == 'E') return "Egreso";
    if (efecto_comprobante == 'N') return "Nomina";
    return "X";
};

const formatStatus = (status) => {
    if (status == 1) return "Vigente";
    if (status == 0) return "Cancelado";
    return "";
};

const sendToNetSuite = () => {
    confirm.require({
        message: '¿Estás seguro de que deseas continuar?',
        header: 'Confirmación',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancelar',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Confirmar'
        },
        accept: () => {
            toast.add({ severity: 'info', summary: 'Confirmado', detail: 'Has aceptado', life: 3000 });
        },
        reject: () => {
            toast.add({ severity: 'error', summary: 'Rechazado', detail: 'Se rechazó la acción', life: 3000 });
        }
    });
};

const provider_type = ref([
    { name: 'Caja chica', id: 8345 },
    { name: 'Varios', id: 8244 },
    { name: 'Viaticos', id: 12185 }
]);
const status_envio = ref([
    { name: 'Pendiente', id: 'pending' },
    { name: 'Canceladas', id: 'trandate_cancel' },
    { name: 'Correctas', id: 'correct' },
    { name: 'Error', id: 'error' }
]);
const tipo_pago = ref([
    { name: 'Pago', id: 'P' },
    { name: 'Ingreso', id: 'I' },
    { name: 'Egreso', id: 'E' },
    { name: 'Nomina', id: 'N' }
]);

const visible_form = ref(false);
const visible_filter = ref(false);

// Filtros ===================================
const selectedStatus = ref('pending');
const selectedPlanta = ref(null);
const storedPlanta = localStorage.getItem("selectedPlanta");
if (storedPlanta) {
    try {
        const parsed = JSON.parse(storedPlanta);
        selectedPlanta.value = parsed.id;
    } catch (e) {
        console.error("Error al parsear selectedPlanta:", e);
    }
}
const selectedTipo = ref(null);
const selectedXml = ref(null);
const selectedPdf = ref(null);
const selectedExcluidos = ref(null);
const selectedVigencia = ref(null);
const selectedDepartamento = ref(null);
const selectedClase = ref(null);
const dates = ref([startOfWeek(new Date(), { weekStartsOn: 1 }), endOfWeek(new Date(), { weekStartsOn: 1 })]);

const filteredInvoices = computed(() => {
    return props.invoices.filter(inv => {
        let ok = true

        // Estado envío
        if (selectedStatus.value) {
            if (selectedStatus.value === 'trandate_cancel') {
                // caso especial: mostrar solo facturas con fecha de cancelación
                if (!inv.trandate_cancel) {
                    ok = false;
                }
            } else {
                if (inv.send_status !== selectedStatus.value) {
                    ok = false;
                }
            }
        }

        // Tipo comprobante
        if (selectedTipo.value && inv.efecto_comprobante !== selectedTipo.value) {
            ok = false
        }

        // Excluidos
        if (selectedExcluidos.value == true && !inv.invoice_exclusion_category_id) {
            ok = false
        }
        if (selectedExcluidos.value == false && inv.invoice_exclusion_category_id) {
            ok = false
        }

        // Estado vigencia
        if (selectedVigencia.value != null && inv.status != selectedVigencia.value) {
            ok = false;
        }

        // XML
        if (selectedXml.value == true && !inv.xml_path) {
            ok = false
        }
        if (selectedXml.value == false && inv.xml_path) {
            ok = false
        }

        // PDF
        if (selectedPdf.value == true && !inv.pdf_path) {
            ok = false
        }
        if (selectedPdf.value == false && inv.pdf_path) {
            ok = false
        }

        // Departamento
        if (selectedDepartamento.value && inv.invoice_department_id !== selectedDepartamento.value) {
            ok = false
        }

        // Clase
        if (selectedClase.value && inv.invoice_class_id !== selectedClase.value) {
            ok = false
        }

        return ok
    })
})

const loadInvoices = () => {
    const [start, end] = dates.value ?? [];

    router.get(route('/xml-table'), {
        planta: selectedPlanta.value,
        dates: start && end ? [
            start.toISOString().slice(0, 10),
            end.toISOString().slice(0, 10)
        ] : null,
    }, {
        preserveState: true, // mantiene filtros y UI
        replace: true,       // evita duplicar historial
    });
};

watch([selectedPlanta, dates], () => {
    loadInvoices();
});

onMounted(() => {
    loadInvoices();
});

// watch(dates, (val) => {
//     const [start, end] = val ?? [];
//     if (start && end) {
//         const formatDate = (d) => {
//             const date = new Date(d);
//             const year = date.getFullYear();
//             const month = String(date.getMonth() + 1).padStart(2, "0");
//             const day = String(date.getDate()).padStart(2, "0");
//             return `${year}-${month}-${day}`;
//         };

//         filters.value.trandate = {
//             value: [formatDate(start), formatDate(end)],
//             matchMode: FilterMatchMode.BETWEEN
//         };
//     } else {
//         delete filters.value.trandate;
//     }
// }, { immediate: true });

console.log(props);

</script>

<template>
    <AppLayout title="Tabla xml">
    <Toast />
    <ConfirmDialog></ConfirmDialog>
            <DataTable 
                :value="filteredInvoices"
                tableStyle="min-width: 50rem"
                class="p-0"
                ref="dt"
                v-model:selection="selectedProducts"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25, 50, 100]"
                currentPageReportTemplate="Mostrando {first} hasta {last} de {totalRecords} registros"
                removableSort
                resizableColumns 
                columnResizeMode="fit"
            >
            <!-- filteredInvoices.length -->
                <Toolbar class="mb-6">
                    <template #start>
                        <div style="text-align:left">
                            
                        </div>
                    </template>

                    <template #end>
                        <Button
                            label="Filtros"
                            size="normal"
                            icon="pi pi-filter"
                            variant="outlined"
                            class="me-2"
                            severity="warn"
                            @click="visible_filter = true"
                        />
                        <MultiSelect
                            v-model="selectedColumns"
                            :options="allColumns"
                            optionLabel="header"
                            display="hidden"
                            :maxSelectedLabels="1"
                            @update:modelValue="onToggle"
                        >
                            <template #value>
                                <i class="pi pi-bars"></i>
                            </template>
                        </MultiSelect>
                    </template>
                </Toolbar>
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Facturas</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>
                <!-- Checkbox de selección -->
                <Column selectionMode="multiple" headerStyle="width: 3rem" />

                <!-- Columnas dinámicas -->
                <template v-for="col in selectedColumns" :key="col.field">
                    <Column :field="col.field" :header="col.header" sortable>
                        <template v-if="col.field === 'acciones'" #body="slotProps">
                            <div class="flex justify-center">
                                <i 
                                    v-if="slotProps.data.loading"
                                    class="pi pi-spin pi-spinner mr-2" 
                                    size="small"
                                />
                            </div>
                            <Button
                                v-if="slotProps.data.xml_path"
                                label="xml"
                                size="small"
                                icon="pi pi-file"
                                variant="outlined"
                                rounded
                                class="mr-2"
                                @click="downloadFile(slotProps.data.xml_path)"
                            />
                            <Button
                                v-if="slotProps.data.pdf_path"
                                label="pdf"
                                size="small"
                                icon="pi pi-file-pdf"
                                variant="outlined"
                                rounded
                                class="mr-2"
                                severity="danger"
                                @click="downloadFile(slotProps.data.pdf_path)"
                            />
                            <Button
                                v-if="slotProps.data.ready_to_netsuite"
                                label="Enviar a netsuite"
                                size="small"
                                icon="pi pi-send"
                                variant="outlined"
                                rounded
                                class="mr-2"
                                severity="success"
                                @click="visible_form = true"
                            />
                            <Button
                                v-if="slotProps.data.reseteable"
                                label="Reiniciar"
                                size="small"
                                icon="pi pi-refresh"
                                variant="outlined"
                                rounded
                                class="mr-2"
                                severity="help"
                                @click="visible_form = true"
                            />
                        </template>

                        <template v-else-if="col.field === 'send_status'" #body="slotProps">
                            <i
                                :class="[getStatusIcon(slotProps.data.send_status), getStatusColor(slotProps.data.send_status), 'cursor-pointer']"
                                :title="statusLabels[slotProps.data.send_status] || statusLabels.default"
                            ></i>
                        </template>

                        <template v-else-if="col.field === 'total'" #body="slotProps">
                            {{ currencyFormatter.format(slotProps.data.total) }}
                        </template>

                        <template v-else-if="col.field === 'invoice_provider_type'" #body="slotProps">
                            {{ formatProviderType(slotProps.data.invoice_provider_type) }}
                        </template>

                        <template v-else-if="col.field === 'efecto_comprobante'" #body="slotProps">
                            {{ formatType(slotProps.data.efecto_comprobante) }}
                        </template>

                        <template v-else-if="col.field === 'status'" #body="slotProps">
                            {{ formatStatus(slotProps.data.status) }}
                        </template>
                    </Column>
                </template>

            </DataTable>
    </AppLayout>

    <!-- Modal Formulario -->
    <Dialog v-model:visible="visible_form" modal header="Datos Factura" :style="{ width: '75rem' }">
        <div class="card border-warning grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="col-span-4 md:col-span-2">
                <label for="invoice_category_id" class="font-bold block mb-2">Categoría</label>
                <Select 
                    :options="props.categories"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_category_id"
                    filter
                    fluid 
                    required/>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Ubicación</label>
                <Select 
                    :options="props.locations"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_location_id"
                    filter
                    fluid 
                    required/>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Departamento</label>
                <Select 
                    :options="props.departments"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_department_id"
                    filter
                    fluid 
                    required/>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Clase</label>
                <Select 
                    :options="props.classes"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_class_id"
                    filter
                    fluid 
                    required/>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Término</label>
                <Select 
                    :options="props.terms"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_term_id"
                    filter
                    fluid 
                    required/>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Tipo de operación</label>
                <Select 
                    :options="props.operationTypes"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_operation_type_id"
                    filter
                    fluid 
                    required/>
            </div>
            <Divider class="col-span-4"/>
            <div class="col-span-4 md:col-span-1">
                <label for="" class="font-bold block mb-2">No de Orden</label>
                <InputText inputId="" fluid />
            </div>
            <div class="col-span-4 md:col-span-3">
                <label for="" class="font-bold block mb-2">Importación</label>
                <Select 
                    :options="props.accountingLists"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_accounting_id"
                    filter
                    fluid />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Artículo</label>
                <Select 
                    :options="props.articles"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_article_id"
                    filter
                    fluid />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Exclusión</label>
                <Select 
                    :options="props.exclusions"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_exclusion_category_id"
                    filter
                    fluid />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Proveedor genérico</label>
                <Select 
                    :options="provider_type"
                    optionLabel="name"
                    optionValue="id"
                    inputId="invoice_provider_type"
                    filter
                    fluid />
            </div>
            <div class="col-span-4">
                <label for="" class="font-bold block mb-2">Notas</label>
                <Textarea rows="5" inputId="" fluid />
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button type="button" icon="pi pi-times" label="Cancelar" severity="secondary" @click="visible_form = false"></Button>
            <Button type="button" icon="pi pi-save" label="Guardar" @click="sendToNetSuite(slotProps.data)"></Button>
        </div>
    </Dialog>
    
    <!-- Modal Filtros -->
    <Dialog v-model:visible="visible_filter" modal header="Filtros" :style="{ width: '75rem' }">
        <div class="card border-warning grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Estado de envío</label>
                <Select 
                    v-model="selectedStatus"
                    :options="status_envio"
                    optionLabel="name"
                    optionValue="id"
                    inputId="filtro_estado_envio"
                    fluid 
                />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Planta</label>
                <Select 
                    v-model="selectedPlanta"
                    :options="props.plantas"
                    optionLabel="code"
                    optionValue="id"
                    inputId="filtro_planta"
                    showClear
                    fluid 
                />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Fechas</label>
                <DatePicker 
                    v-model="dates" 
                    selectionMode="range" 
                    :manualInput="false" 
                    :numberOfMonths="2" 
                    dateFormat="dd/mm/yy" 
                    inputId="filtro_fechas"
                    @update:modelValue="val => dates = val ?? []"
                    fluid
                >
                    <template #footer>
                        <div class="flex justify-between p-2">
                            <Button 
                                label="Hoy" 
                                size="small" 
                                @click="dates = [new Date(), new Date()]" 
                            />
                            <Button 
                                label="Últimos 7 días" 
                                size="small" 
                                @click="dates = [subDays(new Date(), 7), new Date()]" 
                            />
                            <Button 
                                label="Esta semana" 
                                size="small" 
                                @click="dates = [startOfWeek(new Date(), { weekStartsOn: 1 }), endOfWeek(new Date(), { weekStartsOn: 1 })]" 
                            />
                            <Button 
                                label="Este mes" 
                                size="small" 
                                @click="dates = [startOfMonth(new Date()), endOfMonth(new Date())]" 
                            />
                            <Button 
                                label="Mes pasado" 
                                size="small" 
                                @click="dates = [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))]" 
                            />
                            <Button 
                                label="Este año" 
                                size="small" 
                                @click="dates = [startOfYear(new Date()), endOfYear(new Date())]" 
                            />
                        </div>
                        <div class="flex justify-end p-2">
                            <Button 
                                label="Limpiar" 
                                size="small" 
                                severity="secondary"
                                @click="dates = null"
                            />
                        </div>
                    </template>
                </DatePicker>
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Efecto comprobante</label>
                <Select 
                    v-model="selectedTipo"
                    :options="tipo_pago"
                    optionLabel="name"
                    optionValue="id"
                    inputId="filtro_efecto_comprobante"
                    showClear
                    fluid 
                />
            </div>
            <div class="col-span-4 md:col-span-1">
                <label for="" class="font-bold block mb-2">Excluidos</label>
                <Select 
                    v-model="selectedExcluidos"
                    :options="[{ label: 'Sí', value: true }, { label: 'No', value: false }]"
                    optionLabel="label"
                    optionValue="value"
                    inputId="filtro_excluidos"
                    showClear
                    fluid
                />
            </div>
            <div class="col-span-4 md:col-span-1">
                <label for="" class="font-bold block mb-2">Estado vigencia</label>
                <Select 
                    v-model="selectedVigencia"
                    :options="[{ label: 'Vigente', value: 1 }, { label: 'Cancelado', value: 0 }]"
                    optionLabel="label"
                    optionValue="value"
                    inputId="filtro_estado"
                    showClear
                    fluid
                />
            </div>
            <div class="col-span-4 md:col-span-1">
                <label for="" class="font-bold block mb-2">Con XML?</label>
                <Select 
                    v-model="selectedXml"
                    :options="[{ label: 'Sí', value: true }, { label: 'No', value: false }]"
                    optionLabel="label"
                    optionValue="value"
                    inputId="filtro_xml"
                    showClear
                    fluid
                />
            </div>
            <div class="col-span-4 md:col-span-1">
                <label for="" class="font-bold block mb-2">Con PDF?</label>
                <Select
                    v-model="selectedPdf" 
                    :options="[{ label: 'Sí', value: true }, { label: 'No', value: false }]"
                    optionLabel="label"
                    optionValue="value"
                    inputId="filtro_pdf"
                    showClear
                    fluid
                />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Departamento</label>
                <Select 
                    v-model="selectedDepartamento"
                    :options="props.departments"
                    optionLabel="name"
                    optionValue="id"
                    inputId="filtro_departamento"
                    filter
                    showClear
                    fluid
                />
            </div>
            <div class="col-span-4 md:col-span-2">
                <label for="" class="font-bold block mb-2">Clase</label>
                <Select 
                    v-model="selectedClase"
                    :options="props.classes"
                    optionLabel="name"
                    optionValue="id"
                    inputId="filtro_clase"
                    filter
                    showClear
                    fluid 
                />
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button type="button" icon="pi pi-times" label="Cancelar" severity="secondary" @click="visible_filter = false"></Button>
            <Button type="button" icon="pi pi-replay" label="Restablecer" severity="help" @click="visible_filter = false"></Button>
            <!-- <Button type="button" icon="pi pi-filter" label="Aplicar filtros" @click="visible_filter = false"></Button> -->
        </div>
    </Dialog>
</template>

