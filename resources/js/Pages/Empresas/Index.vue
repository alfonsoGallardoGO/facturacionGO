<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales

const { showSuccess, showError } = useToastService();

const props = defineProps({
    empresas: Array,
});

const empresa = useForm({
    id: null,
    name: "",
    code: "",
    account: "",
    currency: "",
    foreign_account: "",
    foreign_currency: "",
});

const currencies = ref([
    { country: "mx", code: "MXN", symbol: "$", label: "Peso mexicano" },
    { country: "us", code: "USD", symbol: "$", label: "Dólar estadounidense" },
    { country: "ca", code: "CAD", symbol: "$", label: "Dólar canadiense" },
    { country: "eu", code: "EUR", symbol: "€", label: "Euro" },
    { country: "gb", code: "GBP", symbol: "£", label: "Libra esterlina" },
]);

const search = ref("");

const selectedEmpresas = ref([]);
const deleteEmpresasDialog = ref(false);
const empresasDialog = ref(false);
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
    if (!search.value) return props.empresas;

    const q = normalize(search.value);
    return props.empresas.filter((item) => {
        return (
            normalize(item.name).includes(q) ||
            normalize(item.code).includes(q) ||
            normalize(item.account).includes(q) ||
            normalize(item.currency).includes(q) ||
            normalize(item.foreign_account).includes(q) ||
            normalize(item.foreign_currency).includes(q)
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
    empresa.name = "";
    empresa.code = "";
    empresa.id = null;
    empresa.account = "";
    empresa.currency = "";
    empresa.foreign_account = "";
    empresa.foreign_currency = "";
    submitted.value = false;
    empresasDialog.value = true;
};

const editEmpresa = (list) => {
    empresa.name = list.name || "";
    empresa.code = list.code || "";
    empresa.id = list.id;
    empresa.account = list.account || "";
    empresa.currency = list.currency || "";
    empresa.foreign_account = list.foreign_account || "";
    empresa.foreign_currency = list.foreign_currency || "";
    empresasDialog.value = true;
};

const saveEmpresa = () => {
    submitted.value = true;

    if (empresa.name && empresa.code) {
        if (empresa.id) {
            empresa.put(`/empresas/${empresa.id}`, {
                onSuccess: () => {
                    empresasDialog.value = false;
                    empresa.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            empresa.post("/empresas", {
                onSuccess: () => {
                    empresasDialog.value = false;
                    empresa.reset();
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

const deleteEmpresa = () => {
    submitted.value = true;
    empresa.delete(`/empresas/${empresa.id}`, {
        onSuccess: () => {
            deleteEmpresasDialog.value = false;
            empresa.reset();
            showSuccess();
            submitted.value = false;
        },
        onError: () => {
            showError();
            submitted.value = false;
        },
    });
};

console.log(props.empresas);
</script>

<template>
    <AppLayout :title="'Empresas'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Empresas</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Empresa"
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
                                    icon="pi pi-id-card"
                                    severity="info"
                                    class="mr-2"
                                    v-if="list.account"
                                    :value="'Numero de cuenta: ' + list.account"
                                ></Tag>
                                <Tag
                                    icon="pi pi-dollar"
                                    severity="success"
                                    :value="'Moneda: ' + list.currency"
                                ></Tag>
                            </div>
                            <div class="mt-2 mb-3">
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
                            </div>
                        </template>
                        <template #footer>
                            <div class="flex gap-4 mt-1">
                                <Button
                                    label="Eliminar"
                                    severity="danger"
                                    class="w-full"
                                    icon="pi pi-trash"
                                    @click="
                                        ((deleteEmpresasDialog = true),
                                        (empresa.id = list.id),
                                        (empresa.name = list.name))
                                    "
                                />
                                <Button
                                    label="Editar"
                                    severity="warn"
                                    icon="pi pi-pencil"
                                    class="w-full"
                                    @click="editEmpresa(list)"
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
                v-model:visible="empresasDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Empresa"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Empresa</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="empresa.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !empresa.name"
                            fluid
                        />
                        <small v-if="empresa.errors.name" class="text-red-500"
                            >El nombre es requerido</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Codigo</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="empresa.code"
                            required
                            :invalid="submitted && !empresa.code"
                            fluid
                        />

                        <small v-if="empresa.errors.code" class="text-red-500"
                            >El codigo no es valido.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Cuenta</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="empresa.account"
                            required
                            :invalid="submitted && !empresa.account"
                            fluid
                        />

                        <small
                            v-if="empresa.errors.account"
                            class="text-red-500"
                            >La cuenta no es valida.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Moneda</label
                        >
                        <Dropdown
                            v-model="empresa.currency"
                            :options="currencies"
                            option-label="label"
                            option-value="code"
                            :filter="true"
                            placeholder="Selecciona moneda"
                            class="w-full md:w-30rem"
                            :show-clear="true"
                        >
                            <!-- cada opción del menú -->
                            <template #option="slotProps">
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="[
                                            'fi',
                                            slotProps.option?.country
                                                ? 'fi-' +
                                                  slotProps.option.country.toLowerCase()
                                                : '',
                                        ]"
                                    />
                                    <span class="font-medium">{{
                                        slotProps.option.label
                                    }}</span>
                                    <span class="opacity-70"
                                        >({{ slotProps.option.code }}
                                        {{ slotProps.option.symbol }})</span
                                    >
                                </div>
                            </template>

                            <!-- valor seleccionado (slotProps.value es EL CÓDIGO, no el objeto) -->
                            <template #value="slotProps">
                                <template v-if="slotProps.value">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'fi',
                                                currencies.find(
                                                    (c) =>
                                                        c.code ===
                                                        slotProps.value,
                                                )?.country || ''
                                                    ? 'fi-' +
                                                      currencies
                                                          .find(
                                                              (c) =>
                                                                  c.code ===
                                                                  slotProps.value,
                                                          )
                                                          .country.toLowerCase()
                                                    : '',
                                            ]"
                                        />
                                        <span>{{
                                            currencies.find(
                                                (c) =>
                                                    c.code === slotProps.value,
                                            )?.label
                                        }}</span>
                                        <span class="opacity-70">
                                            ({{ slotProps.value }}
                                            {{
                                                currencies.find(
                                                    (c) =>
                                                        c.code ===
                                                        slotProps.value,
                                                )?.symbol
                                            }})
                                        </span>
                                    </div>
                                </template>
                                <span v-else>Selecciona moneda</span>
                            </template>
                        </Dropdown>
                        <small
                            v-if="empresa.errors.currency"
                            class="text-red-500"
                            >La moneda no es valida.</small
                        >
                    </div>

                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Cuenta Foranea</label
                        >
                        <InputText
                            id="code"
                            v-model.trim="empresa.foreign_account"
                            required
                            :invalid="submitted && !empresa.foreign_account"
                            fluid
                        />

                        <small
                            v-if="empresa.errors.foreign_account"
                            class="text-red-500"
                            >La cuenta foranea no es valida.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Moneda Foranea</label
                        >
                        <Dropdown
                            v-model="empresa.foreign_currency"
                            :options="currencies"
                            option-label="label"
                            option-value="code"
                            :filter="true"
                            placeholder="Selecciona moneda"
                            class="w-full md:w-30rem"
                            :show-clear="true"
                        >
                            <!-- cada opción del menú -->
                            <template #option="slotProps">
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="[
                                            'fi',
                                            slotProps.option?.country
                                                ? 'fi-' +
                                                  slotProps.option.country.toLowerCase()
                                                : '',
                                        ]"
                                    />
                                    <span class="font-medium">{{
                                        slotProps.option.label
                                    }}</span>
                                    <span class="opacity-70"
                                        >({{ slotProps.option.code }}
                                        {{ slotProps.option.symbol }})</span
                                    >
                                </div>
                            </template>

                            <!-- valor seleccionado (slotProps.value es EL CÓDIGO, no el objeto) -->
                            <template #value="slotProps">
                                <template v-if="slotProps.value">
                                    <div class="flex items-center gap-2">
                                        <span
                                            :class="[
                                                'fi',
                                                currencies.find(
                                                    (c) =>
                                                        c.code ===
                                                        slotProps.value,
                                                )?.country || ''
                                                    ? 'fi-' +
                                                      currencies
                                                          .find(
                                                              (c) =>
                                                                  c.code ===
                                                                  slotProps.value,
                                                          )
                                                          .country.toLowerCase()
                                                    : '',
                                            ]"
                                        />
                                        <span>{{
                                            currencies.find(
                                                (c) =>
                                                    c.code === slotProps.value,
                                            )?.label
                                        }}</span>
                                        <span class="opacity-70">
                                            ({{ slotProps.value }}
                                            {{
                                                currencies.find(
                                                    (c) =>
                                                        c.code ===
                                                        slotProps.value,
                                                )?.symbol
                                            }})
                                        </span>
                                    </div>
                                </template>
                                <span v-else>Selecciona moneda</span>
                            </template>
                        </Dropdown>
                        <small
                            v-if="empresa.errors.foreign_currency"
                            class="text-red-500"
                            >La moneda foranea no es valida.</small
                        >
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancelar"
                        icon="pi pi-times"
                        text
                        @click="empresasDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveEmpresa"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteEmpresasDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span
                        >Estas seguro que deseas eliminar la empresa
                        <b>{{ empresa.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteEmpresasDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteEmpresa"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
