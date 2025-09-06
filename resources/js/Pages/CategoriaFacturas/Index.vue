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
    categories: Array,
});

const categories = useForm({
    name: "",
    code: "",
});

const selectedCategories = ref([]);
const deleteCategoryDialog = ref(false);
const categoriesDialog = ref(false);
const submitted = ref(false);

const openNew = () => {
    categories.name = "";
    categories.code = "";
    categories.id = null;
    categoriesDialog.value = true;
};

const editCategories = (list) => {
    categories.name = list.name;
    categories.code = list.code;
    categories.id = list.id;
    categoriesDialog.value = true;
};

const saveCategory = () => {
    submitted.value = true;

    if (categories.name && categories.code) {
        if (categories.id) {
            categories.put(`/categoria-facturas/${categories.id}`, {
                onSuccess: () => {
                    categoriesDialog.value = false;
                    categories.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            categories.post("/categoria-facturas", {
                onSuccess: () => {
                    categoriesDialog.value = false;
                    categories.reset();
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

const confirmDelteCategories = (category) => {
    deleteCategoryDialog.value = true;
    categories.id = category.id;
    categories.name = category.name;
};

const deleteCategories = () => {
    submitted.value = true;
    if (selectedCategories.value.length) {
        // Eliminar multiples
        const ids = selectedCategories.value.map((cat) => cat.id);
        console.log(ids);
        Inertia.post(
            "/categoria-facturas/delete-multiple",
            { ids: ids },
            {
                onSuccess: () => {
                    deleteCategoryDialog.value = false;
                    categories.reset();
                    selectedCategories.value = [];
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
        categories.delete(`/categoria-facturas/${categories.id}`, {
            onSuccess: () => {
                deleteCategoryDialog.value = false;
                categories.reset();
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

console.log(props.categories);
</script>

<template>
    <AppLayout :title="'Categorias de Factura'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Categorias de Factura</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Categoria"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="openNew"
                />

                <Button
                    label="Eliminar seleccionados"
                    icon="pi pi-trash"
                    severity="danger"
                    variant="outlined"
                    @click="confirmDelteCategories"
                    :disabled="
                        !selectedCategories || !selectedCategories.length
                    "
                />
            </template>
        </Toolbar>
        <div class="card">
            <DataTable
                ref="dt"
                v-model:selection="selectedCategories"
                :value="props.categories"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} categorias"
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
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-pencil"
                            variant="outlined"
                            rounded
                            class="mr-2"
                            @click="editCategories(slotProps.data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            variant="outlined"
                            rounded
                            severity="danger"
                            @click="confirmDelteCategories(slotProps.data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="categoriesDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Categoria"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Categoria</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="categories.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !categories.name"
                            fluid
                        />
                        <small
                            v-if="categories.errors.name"
                            class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="categories.code"
                            required
                            :invalid="submitted && !categories.code"
                            fluid
                        />

                        <small
                            v-if="categories.errors.code"
                            class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="categoriesDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveCategory"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteCategoryDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="!selectedCategories.length"
                        >Estas seguro que deseas eliminar la lista de
                        contabilidad <b>{{ categories.name }}</b
                        >?</span
                    >
                    <span v-if="selectedCategories.length"
                        >Estas seguro que deseas eliminar las categorias
                        seleccionadas ?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteCategoryDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteCategories"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
