<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales

const { showSuccess, showError } = useToastService();

const props = defineProps({
    articles: Array,
});

const articles = useForm({
    name: "",
    code: "",
});

const search = ref("");

const selectedAccountingLists = ref([]);
const deleteArticleDialog = ref(false);
const articlesDialog = ref(false);
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
    if (!search.value) return props.articles;

    const q = normalize(search.value);
    return props.articles.filter((item) => {
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
    articles.name = "";
    articles.code = "";
    articles.id = null;
    articlesDialog.value = true;
};

const editArticles = (list) => {
    articles.name = list.name;
    articles.code = list.code;
    articles.id = list.id;
    articlesDialog.value = true;
};

const saveArticle = () => {
    submitted.value = true;

    if (articles.name && articles.code) {
        if (articles.id) {
            articles.put(`/articulos/${articles.id}`, {
                onSuccess: () => {
                    articlesDialog.value = false;
                    articles.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            articles.post("/articulos", {
                onSuccess: () => {
                    articlesDialog.value = false;
                    articles.reset();
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

const deleteArticle = () => {
    submitted.value = true;
    articles.delete(`/articulos/${articles.id}`, {
        onSuccess: () => {
            deleteArticleDialog.value = false;
            articles.reset();
            showSuccess();
            submitted.value = false;
        },
        onError: () => {
            showError();
            submitted.value = false;
        },
    });
};

console.log(props.articles);
</script>

<template>
    <AppLayout :title="'Articulos'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Articulos</h2>
            </template>

            <template #end>
                <Button
                    label="Añadir Articulo"
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
                                        ((deleteArticleDialog = true),
                                        (articles.id = list.id),
                                        (articles.name = list.name))
                                    "
                                />
                                <Button
                                    label="Editar"
                                    severity="warn"
                                    icon="pi pi-pencil"
                                    class="w-full"
                                    @click="editArticles(list)"
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
                v-model:visible="articlesDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Articulo"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Articulo</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="articles.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !articles.name"
                            fluid
                        />
                        <small v-if="articles.errors.name" class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="articles.code"
                            required
                            :invalid="submitted && !articles.code"
                            fluid
                        />

                        <small v-if="articles.errors.code" class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="articlesDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveArticle"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteArticleDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span
                        >Estas seguro que deseas eliminar la lista de
                        contabilidad <b>{{ articles.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteArticleDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteArticle"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
