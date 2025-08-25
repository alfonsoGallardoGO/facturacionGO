<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted } from "vue";
import { FilterMatchMode } from '@primevue/core/api';

const products = ref([]);
const selectedProducts = ref([]);

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
});

const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const exportCSV = () => {
    dt.value.exportCSV();
};

const currencyFormatter = new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
});

const downloadFile = (path) => {
    console.log(`/spaces/test-download?path=${encodeURIComponent(path)}`);
    if (!path) {
        alert("No hay archivo disponible");
        return;
    }
    window.location.href = `/spaces/test-download?path=${encodeURIComponent(path)}`;
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
        case 'pending': return 'text-yellow-500'; // color amarillo
        case 'error': return 'text-red-500';       // color rojo
        case 'correct': return 'text-green-500';   // color verde
        default: return 'text-gray-400';           // gris para unknown
    }
};

const statusLabels = {
    pending: 'Pendiente',
    error: 'Error',
    correct: 'Correcto',
    default: 'Desconocido',
};

console.log(props.invoices);

// onMounted(async () => {
//     try {
//         const res = await fetch(
//             "https://my.api.mockaroo.com/dtpv.json?key=a0e7b470",
//             {
//                 headers: {
//                     Accept: "application/json",
//                 },
//             },
//         );
//         console.log(res);
//         products.value = await res.json();
//         console.log("Datos cargados:", products.value);
//     } catch (error) {
//         console.error("Error cargando datos:", error);
//     }
// });
</script>

<template>
    <AppLayout title="Tabla xml">
            <DataTable 
                :value="props.invoices"
                tableStyle="min-width: 50rem"
                class="p-0"
                ref="dt"
                v-model:selection="selectedProducts"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25, 50, 100, products.length]"
                currentPageReportTemplate="Mostrando {first} hasta {last} de {totalRecords} registros"
                removableSort
                resizableColumns 
                columnResizeMode="fit"
            >
                <Toolbar class="mb-6">
                    <!-- <template #start>
                        <Button label="New" icon="pi pi-plus" class="mr-2" @click="openNew" />
                        <Button label="Delete" icon="pi pi-trash" severity="danger" variant="outlined"
                            @click="confirmDeleteSelected" :disabled="!selectedProducts || !selectedProducts.length" />
                    </template> -->

                    <template #end>
                        <!-- <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" customUpload
                            chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" /> -->
                        <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
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
                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column sortable field="acciones" header="Acciones">
                    <template #body="slotProps">
                        <Button
                            label="xml"
                            size="small"
                            icon="pi pi-file"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="downloadFile(slotProps.data.xml_path)"
                        />
                        <Button
                            label="pdf"
                            size="small"
                            icon="pi pi-file-pdf"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="downloadFile(slotProps.data.pdf_path)"
                        />
                    </template>
                </Column>
                <Column field="send_status" header="Estado">
                    <template #body="slotProps">
                        <i
                        :class="[
                            getStatusIcon(slotProps.data.send_status),
                            getStatusColor(slotProps.data.send_status),
                            'cursor-pointer'
                        ]"
                        :title="statusLabels[slotProps.data.send_status] || statusLabels.default"
                        ></i>
                    </template>
                </Column>
                <Column sortable field="empresa" header="Empresa" />
                <Column sortable field="uuid" header="Uuid" />
                <Column sortable field="emisor_rfc" header="Emisor RFC" />
                <Column sortable field="emisor_name" header="Emisor Nombre" />
                <Column sortable field="trandate" header="Fecha de emisión" />
                <Column sortable field="fecha_certificacion" header="Fecha de certificación" />
                <Column sortable field="pac_certifico" header="PAC que Certificó" />
                <Column sortable field="total" header="Importe">
                    <template #body="slotProps">
                        {{ currencyFormatter.format(slotProps.data.total) }}
                    </template>
                </Column>
                <Column sortable field="fecha_cancelacion" header="Fecha de cancelación" />
                <Column sortable field="no_orden" header="No de Orden" />
                <Column sortable field="categoria" header="Categoría" />
                <Column sortable field="ubicacion" header="Ubicación" />
                <Column sortable field="departamento" header="Departamento" />
                <Column sortable field="clase" header="Clase" />
                <Column sortable field="notas" header="Notas" />
                <Column sortable field="termino" header="Término" />
                <Column sortable field="importacion" header="Importación" />
                <Column sortable field="articulo" header="Artículo" />
                <Column sortable field="exclusion" header="Exclusión" />
                <Column sortable field="proveedor_generico" header="Proveedor genérico" />
                <Column sortable field="tipo_operacion" header="Tipo de operación" />
                <Column sortable field="tipo" header="Tipo" />
                <Column sortable field="estatus" header="Estado" />
            </DataTable>
    </AppLayout>
</template>
