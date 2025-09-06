<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales
import { compileAsync } from "sass";

const { showSuccess, showError } = useToastService();

const props = defineProps({
    ubicaciones: Array,
    cities: Array,
    companies: Array,
});

const ubicacionesCopy = ref([...props.ubicaciones]);

const ubicacion = useForm({
    id: null,
    name: "",
    code: "",
    invoice_company_id: "",
    city_id: "",
});

const ubicacionAuto = ref({ city: null, city_id: null });
const companiaAuto = ref({ company: null, company_id: null });
const search = ref("");

const suggestions = ref([]); // lo que ve el AutoComplete
const loading = ref(false);
const q = ref("");

const perPage = 10;
const page = ref(1);
const hasMore = ref(false);

let typingTimer = null;
let allMatches = [];

function onComplete(e) {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        loading.value = true;
        q.value = (e.query || "").toLowerCase().trim();

        // 1) filtra en memoria
        if (!q.value) {
            allMatches = [...props.cities]; // sin filtro: todas
        } else {
            allMatches = props.cities.filter((c) =>
                c.name.toLowerCase().includes(q.value),
            );
        }

        // 2) pagina en bloques de 10
        page.value = 1;
        const firstSlice = allMatches.slice(0, perPage);

        hasMore.value = allMatches.length > perPage;

        // 3) muestra resultados + “Cargar más”
        suggestions.value = [
            ...firstSlice,
            ...(hasMore.value ? [{ __more: true }] : []),
        ];
        loading.value = false;
    }, 200);
}

function loadMore() {
    if (!hasMore.value) return;
    page.value += 1;

    const next = allMatches.slice(
        (page.value - 1) * perPage,
        page.value * perPage,
    );

    // quita el placeholder y vuelve a agregar si aún hay más
    suggestions.value = suggestions.value.filter((i) => !i.__more);
    suggestions.value.push(...next);
    hasMore.value = allMatches.length > page.value * perPage;
    if (hasMore.value) suggestions.value.push({ __more: true });
}

function onSelect(city) {
    ubicacionAuto.value.city = city;
    ubicacionAuto.value.city_id = city?.id ?? null;

    ubicacion.city_id = city?.id ?? null;
}

const deleteUbicacionDialog = ref(false);
const ubicacionDialog = ref(false);
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
    if (!search.value) return props.ubicaciones;

    const q = normalize(search.value);
    return props.ubicaciones.filter((item) => {
        return (
            normalize(item.name).includes(q) ||
            normalize(item.code).includes(q) ||
            normalize(item.city_name).includes(q) ||
            normalize(item.company_name).includes(q)
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
    ubicacion.id = null;
    ubicacion.name = "";
    ubicacion.code = "";
    ubicacion.invoice_company_id = "";
    ubicacion.city_id = "";
    ubicacionAuto.value.city = null;
    companiaAuto.value.company = null;
    submitted.value = false;
    ubicacionDialog.value = true;
};

const editUbicacion = (list) => {
    ubicacion.id = list.id;
    ubicacion.name = list.name;
    ubicacion.code = list.code;
    ubicacionAuto.value.city = props.cities.find(
        (c) => c.name === list.city_name,
    );
    companiaAuto.value.company = props.companies.find(
        (c) => c.name === list.company_name,
    );
    ubicacion.city_id = ubicacionAuto.value.city?.id ?? null;
    ubicacion.invoice_company_id = companiaAuto.value.company?.id ?? null;

    console.log(ubicacion);
    ubicacionDialog.value = true;
};

const saveUbicacion = () => {
    submitted.value = true;

    if (ubicacion.name && ubicacion.code) {
        if (ubicacion.id) {
            ubicacion.put(`/ubicaciones/${ubicacion.id}`, {
                onSuccess: () => {
                    ubicacionDialog.value = false;
                    ubicacion.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            ubicacion.post("/ubicaciones", {
                onSuccess: () => {
                    ubicacionDialog.value = false;
                    ubicacion.reset();
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

const deleteUbicacion = () => {
    submitted.value = true;
    ubicacion.delete(`/ubicaciones/${ubicacion.id}`, {
        onSuccess: () => {
            deleteUbicacionDialog.value = false;
            ubicacion.reset();
            showSuccess();
            submitted.value = false;
        },
        onError: () => {
            showError();
            submitted.value = false;
        },
    });
};

const changeCompany = (e) => {
    console.log(e);

    companiaAuto.value.company = e.value
        ? props.companies.find((c) => c.id === e.value)
        : null;
    ubicacion.invoice_company_id = e.value ?? null;
    console.log(companiaAuto.value.company.name);
    console.log(ubicacion);
};

console.log(props.ubicaciones, props.cities, props.companies);
</script>

<template>
    <AppLayout :title="'Ubicaciones'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Ubicaciones</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Ubicacion"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="openNew"
                />
            </template>
        </Toolbar>
        <div class="card border-none">
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
                                class="mr-2"
                                :value="'Codigo: ' + list.code"
                            ></Tag>
                            <div class="mt-5">
                                <Tag
                                    icon="pi pi-map-marker"
                                    severity="info"
                                    class="mr-2"
                                    :value="'Ciudad: ' + list.city_name"
                                ></Tag>
                                <Tag
                                    icon="pi pi-building"
                                    severity="warn"
                                    :value="'Compañia: ' + list.company_name"
                                ></Tag>
                            </div>
                            <!-- <div class="mt-2 mb-3">
                                <Tag
                                    icon="pi pi-id-card"
                                    severity="warn"
                                    class="mr-2"
                                    v-if="list.foreign_account"
                                    :value="
                                        'Cuenta foranea: ' +
                                        list.foreign_account
                                    "
                                ></Tag>
                                <Tag
                                    icon="pi pi-globe"
                                    severity="danger"
                                    :value="
                                        'Moneda foranea: ' +
                                        list.foreign_currency
                                    "
                                ></Tag>
                            </div> -->
                        </template>
                        <template #footer>
                            <div class="flex gap-4 mt-1">
                                <Button
                                    label="Eliminar"
                                    severity="danger"
                                    class="w-full"
                                    icon="pi pi-trash"
                                    @click="
                                        ((deleteUbicacionDialog = true),
                                        (ubicacion.id = list.id),
                                        (ubicacion.name = list.name))
                                    "
                                />
                                <Button
                                    label="Editar"
                                    severity="warn"
                                    icon="pi pi-pencil"
                                    class="w-full"
                                    @click="editUbicacion(list)"
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
                v-model:visible="ubicacionDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Ubicacion"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Ubicacion</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="ubicacion.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !ubicacion.name"
                            fluid
                        />
                        <small v-if="ubicacion.errors.name" class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="ubicacion.code"
                            required
                            :invalid="submitted && !ubicacion.code"
                            fluid
                        />

                        <small v-if="ubicacion.errors.code" class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Compañia</label
                        >
                        <Dropdown
                            v-model="companiaAuto.company"
                            @change="changeCompany($event)"
                            :options="props.companies"
                            option-label="name"
                            option-value="id"
                            placeholder="Seleccione una ciudad"
                            :filter="true"
                            filter-placeholder="Buscar ciudad"
                            filter-input-autofocus
                            required
                            :invalid="submitted && !ubicacion.city_id"
                            fluid
                        >
                            <!-- Valor seleccionado -->
                            <template #value="slotProps">
                                <span v-if="slotProps.value">{{
                                    companiaAuto.company?.name
                                }}</span>
                                <span v-else class="opacity-60"
                                    >Seleccione una compañia</span
                                >
                            </template>
                        </Dropdown>

                        <small
                            v-if="ubicacion.errors.invoice_company_id"
                            class="text-red-500"
                            >La compañia no es valida.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Ciudad</label
                        >

                        <AutoComplete
                            v-model="ubicacionAuto.city"
                            optionLabel="name"
                            placeholder="Seleccione una ciudad"
                            :suggestions="suggestions"
                            :completeOnFocus="true"
                            :loading="loading"
                            fluid
                            @complete="onComplete"
                            @item-select="(e) => onSelect(e.value)"
                        >
                            <template #option="slotProps">
                                <Button
                                    v-if="slotProps.option.__more"
                                    type="button"
                                    icon="pi pi-plus"
                                    label="Cargar más"
                                    fluid
                                    @click.stop.prevent="loadMore"
                                >
                                    Cargar más
                                </Button>
                            </template>
                            <!-- Valor seleccionado -->
                            <template #value="slotProps">
                                <span v-if="slotProps.value">{{
                                    ubicacionAuto.city?.name
                                }}</span>
                                <span v-else class="opacity-60"
                                    >Seleccione una ciudad</span
                                >
                            </template>
                        </AutoComplete>

                        <small
                            v-if="ubicacion.errors.city_id"
                            class="text-red-500"
                            >La ciudad no es valida.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="ubicacionDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveUbicacion"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteUbicacionDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span
                        >Estas seguro que deseas eliminar la empresa
                        <b>{{ ubicacion.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteUbicacionDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteUbicacion"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
