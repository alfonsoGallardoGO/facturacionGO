<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted } from "vue";

const products = ref([]);

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

console.log(props.invoices);

onMounted(async () => {
    try {
        const res = await fetch(
            "https://my.api.mockaroo.com/dtpv.json?key=a0e7b470",
            {
                headers: {
                    Accept: "application/json",
                },
            },
        );
        console.log(res);
        products.value = await res.json();
        console.log("Datos cargados:", products.value);
    } catch (error) {
        console.error("Error cargando datos:", error);
    }
});
</script>

<template>
    <AppLayout title="Tabla xml">
        <div class="card">
            <DataTable :value="props.invoices" tableStyle="min-width: 50rem">
                <Column field="id" header="ID" />
                <Column field="estatus" header="Estatus" />
                <Column field="empresa" header="Empresa" />
                <Column field="uuid" header="Uuid" />
                <Column field="emisor_rfc" header="Emisor RFC" />
                <Column field="emisor_nombre" header="Emisor Nombre" />
                <Column field="fecha_emision" header="Fecha de emisión" />
                <Column
                    field="fecha_certificacion"
                    header="Fecha de certificación"
                />
                <Column field="pac_certifico" header="PAC que Certificó" />
                <Column field="importe" header="Importe" />
                <Column
                    field="fecha_cancelacion"
                    header="Fecha de cancelación"
                />
                <Column field="no_orden" header="No de Orden" />
                <Column field="categoria" header="Categoría" />
                <Column field="ubicacion" header="Ubicación" />
                <Column field="departamento" header="Departamento" />
                <Column field="clase" header="Clase" />
                <Column field="notas" header="Notas" />
                <Column field="termino" header="Término" />
                <Column field="importacion" header="Importación" />
                <Column field="articulo" header="Artículo" />
                <Column field="exclusion" header="Exclusión" />
                <Column
                    field="proveedor_generico"
                    header="Proveedor genérico"
                />
                <Column field="tipo_operacion" header="Tipo de operación" />
                <Column field="tipo" header="Tipo" />
            </DataTable>
        </div>
    </AppLayout>
</template>
