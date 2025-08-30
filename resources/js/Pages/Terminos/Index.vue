<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales

const { showSuccess, showError } = useToastService();

const props = defineProps({
    terms: Array,
});

const term = useForm({
    name: "",
    code: "",
});

const search = ref("");

const deleteTermDialog = ref(false);
const termDialog = ref(false);
const submitted = ref(false);

const first = ref(0);
const rows = ref(9);

const normalize = (s) =>
    (s ?? "")
        .toString()
        .normalize("NFD")
        .replace(/\p{Diacritic}/gu, "")
        .toLowerCase();

const filteredLists = computed(() => {
    if (!search.value) return props.terms;

    const q = normalize(search.value);
    return props.terms.filter((item) => {
        return (
            normalize(item.name).includes(q) || normalize(item.code).includes(q)
        );
    });
});

const pagedLists = computed(() => {
    const start = first.value;
    const end = start + rows.value;
    return filteredLists.value.slice(start, end);
});

const totalRecords = computed(() => filteredLists.value.length);

function onSearch() {
    first.value = 0;
}

function onPage(e) {
    first.value = e.first;
    rows.value = e.rows;
}

watch([rows, filteredLists], () => {
    if (first.value >= totalRecords.value) first.value = 0;
});

const openNew = () => {
    term.name = "";
    term.code = "";
    term.id = null;
    termDialog.value = true;
};

const editTerm = (list) => {
    term.name = list.name;
    term.code = list.code;
    term.id = list.id;
    termDialog.value = true;
};

const saveTerm = () => {
    submitted.value = true;

    if (term.name && term.code) {
        if (term.id) {
            term.put(`/terminos-pago/${term.id}`, {
                onSuccess: () => {
                    termDialog.value = false;
                    term.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            term.post("/terminos-pago", {
                onSuccess: () => {
                    termDialog.value = false;
                    term.reset();
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

const deleteTerm = () => {
    submitted.value = true;
    term.delete(`/terminos-pago/${term.id}`, {
        onSuccess: () => {
            deleteTermDialog.value = false;
            term.reset();
            showSuccess();
            submitted.value = false;
        },
        onError: () => {
            showError();
            submitted.value = false;
        },
    });
};

console.log(props.terms);
</script>

<template>
    <AppLayout :title="'Términos de Pago'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Terminos de pago</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Termino"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="openNew"
                />
            </template>
        </Toolbar>
        <div class="card">
            <div class="flex justify-content-end mb-4">
                <IconField>
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText
                        placeholder="Buscar..."
                        v-model="search"
                        @input="onSearch"
                        fluid
                    />
                </IconField>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div v-for="list in pagedLists" :key="list.id" class="">
                    <Card>
                        <template #title
                            >{{ list.name }}
                            <Tag
                                severity="info"
                                :value="'Codigo: ' + list.code"
                            ></Tag>
                        </template>
                        <template #footer>
                            <div class="flex gap-4 mt-1">
                                <Button
                                    label="Eliminar"
                                    severity="danger"
                                    class="w-full"
                                    icon="pi pi-trash"
                                    @click="
                                        ((deleteTermDialog = true),
                                        (term.id = list.id),
                                        (term.name = list.name))
                                    "
                                />
                                <Button
                                    label="Editar"
                                    severity="warn"
                                    icon="pi pi-pencil"
                                    class="w-full"
                                    @click="editTerm(list)"
                                />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
            <Paginator
                :first="first"
                :rows="rows"
                :totalRecords="totalRecords"
                @page="onPage"
            />

            <Dialog
                v-model:visible="termDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Termino de Pago"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Termino de pago</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="term.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !term.name"
                            fluid
                        />
                        <small v-if="term.errors.name" class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="term.code"
                            required
                            :invalid="submitted && !term.code"
                            fluid
                        />

                        <small v-if="term.errors.code" class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="termDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveTerm"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteTermDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span
                        >Estas seguro que deseas eliminar el termino de pago
                        <b>{{ term.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteTermDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteTerm"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
