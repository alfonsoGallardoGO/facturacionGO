<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales
import { FilterMatchMode } from "@primevue/core/api";
import { router as Inertia } from "@inertiajs/vue3";

const { showSuccess, showError } = useToastService();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const props = defineProps({
    classes: Array,
});

const classes = useForm({
    name: "",
    code: "",
    active: false,
});

const selectedClasses = ref([]);
const deleteClassesDialog = ref(false);
const classesDialog = ref(false);
const submitted = ref(false);

const openNew = () => {
    classes.name = "";
    classes.code = "";
    classes.id = null;
    classes.active = false;
    classesDialog.value = true;
};

const editClasses = (list) => {
    classes.name = list.name;
    classes.code = list.code;
    classes.id = list.id;
    classes.active = list.active === 1 ? true : false;
    classesDialog.value = true;
};

const saveClass = () => {
    submitted.value = true;

    if (classes.name && classes.code) {
        if (classes.id) {
            classes.put(`/clases-facturacion/${classes.id}`, {
                onSuccess: () => {
                    classesDialog.value = false;
                    classes.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            classes.post("/clases-facturacion", {
                onSuccess: () => {
                    classesDialog.value = false;
                    classes.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        }
    }
};

const confirmDelteClasses = (clas) => {
    deleteClassesDialog.value = true;
    classes.id = clas.id;
    classes.name = clas.name;
};

const deleteClasses = () => {
    submitted.value = true;
    if (selectedClasses.value.length) {
        // Eliminar multiples
        const ids = selectedClasses.value.map((cat) => cat.id);
        console.log(ids);
        Inertia.post(
            "/clases-facturacion/delete-multiple",
            { ids: ids },
            {
                onSuccess: () => {
                    deleteClassesDialog.value = false;
                    classes.reset();
                    selectedClasses.value = [];
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            },
        );
    } else {
        // Eliminar una sola
        classes.delete(`/clases-facturacion/${classes.id}`, {
            onSuccess: () => {
                deleteClassesDialog.value = false;
                classes.reset();
                showSuccess();
                submitted.value = false;
            },
            onError: () => {
                showError();
                submitted.value = false;
            },
        });
    }
};

console.log(props.classes);
</script>
<template>
    <AppLayout :title="'Clases de Factura'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Clases de Factura</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Clase"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="openNew"
                />

                <Button
                    label="Eliminar seleccionados"
                    icon="pi pi-trash"
                    severity="danger"
                    variant="outlined"
                    @click="confirmDelteClasses"
                    :disabled="!selectedClasses || !selectedClasses.length"
                />
            </template>
        </Toolbar>
        <div class="card">
            <DataTable
                ref="dt"
                v-model:selection="selectedClasses"
                :value="props.classes"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} clases"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                v-model="filters['global'].value"
                                placeholder="Buscar..."
                            />
                        </IconField>
                    </div>
                </template>

                <Column
                    selectionMode="multiple"
                    style="width: 3rem"
                    :exportable="false"
                ></Column>
                <Column
                    field="code"
                    header="Codigo"
                    sortable
                    style="min-width: 12rem"
                ></Column>
                <Column
                    field="name"
                    header="Categoria"
                    sortable
                    style="min-width: 16rem"
                ></Column>
                <Column
                    field="active"
                    header="Estado"
                    sortable
                    style="min-width: 16rem"
                >
                    <template #body="slotProps">
                        <Tag
                            :value="
                                slotProps.data.active === 1
                                    ? 'Activo'
                                    : 'Desactivado'
                            "
                            :severity="
                                slotProps.data.active === 1
                                    ? 'success'
                                    : 'danger'
                            "
                        ></Tag>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editClasses(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDelteClasses(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="classesDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Clase"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Clase</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="classes.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !classes.name"
                            fluid
                        />
                        <small v-if="classes.errors.name" class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="classes.code"
                            required
                            :invalid="submitted && !classes.code"
                            fluid
                        />

                        <small v-if="classes.errors.code" class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Estado</label
                        >
                        <ToggleButton
                            v-model="classes.active"
                            onLabel="Activo"
                            offLabel="Desactivado"
                            onIcon="pi pi-check"
                            offIcon="pi pi-times"
                        />
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="classesDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveClass"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteClassesDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="!selectedClasses.length"
                        >Estas seguro que deseas eliminar la clase
                        <b>{{ classes.name }}</b
                        >?</span
                    >
                    <span v-if="selectedClasses.length"
                        >Estas seguro que deseas eliminar las clases
                        seleccionadas ?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteClassesDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteClasses"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
