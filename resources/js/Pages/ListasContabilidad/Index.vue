<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { useToastService } from "../../Stores/toastService.js"; // importar para llamar a los mensajes globales

const { showSuccess, showError } = useToastService();

const props = defineProps({
    accountingLists: Array,
});

const accountList = useForm({
    name: "",
    code: "",
});

const selectedAccountingLists = ref([]);
const deleteAccountingDialog = ref(false);
const listDialog = ref(false);
const submitted = ref(false);

const first = ref(0);
const rows = ref(9);

const pagedLists = computed(() => {
    const start = first.value;
    const end = start + rows.value;
    return props.accountingLists.slice(start, end);
});

const openNew = () => {
    accountList.name = "";
    accountList.code = "";
    accountList.id = null;
    listDialog.value = true;
};

const editProduct = (list) => {
    accountList.name = list.name;
    accountList.code = list.code;
    accountList.id = list.id;
    listDialog.value = true;
};

const saveAccountingList = () => {
    submitted.value = true;

    if (accountList.name && accountList.code) {
        if (accountList.id) {
            accountList.put(`/listas-contabilidad/${accountList.id}`, {
                onSuccess: () => {
                    listDialog.value = false;
                    accountList.reset();
                    showSuccess();
                    submitted.value = false;
                },
                onError: () => {
                    showError();
                    submitted.value = false;
                },
            });
        } else {
            accountList.post("/listas-contabilidad", {
                onSuccess: () => {
                    listDialog.value = false;
                    accountList.reset();
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

const deleteAccountingLists = () => {
    submitted.value = true;
    accountList.delete(`/listas-contabilidad/${accountList.id}`, {
        onSuccess: () => {
            deleteAccountingDialog.value = false;
            accountList.reset();
            showSuccess();
            submitted.value = false;
        },
        onError: () => {
            showError();
            submitted.value = false;
        },
    });
};

console.log(props.accountingLists);
</script>

<template>
    <AppLayout :title="'Listas de Contabilidad'">
        <Toolbar class="mb-6 px-10">
            <template #start>
                <h2 class="mb-0">Listas de Contabilidad</h2>
            </template>
            <template #end>
                <Button
                    label="Añadir Lista"
                    icon="pi pi-plus"
                    class="mr-2"
                    @click="openNew"
                />
            </template>
        </Toolbar>
        <div class="card">
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
                                        ((deleteAccountingDialog = true),
                                        (accountList.id = list.id),
                                        (accountList.name = list.name))
                                    "
                                />
                                <Button
                                    label="Editar"
                                    severity="warn"
                                    icon="pi pi-pencil"
                                    class="w-full"
                                    @click="editProduct(list)"
                                />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
            <Paginator
                v-model:first="first"
                v-model:rows="rows"
                :totalRecords="props.accountingLists.length"
            />

            <Dialog
                v-model:visible="listDialog"
                :style="{ width: '450px' }"
                header="Añadir o Editar Lista de Contabilidad"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Lista de contabilidad</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="accountList.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !accountList.name"
                            fluid
                        />
                        <small
                            v-if="accountList.errors.name"
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
                            v-model.trim="accountList.code"
                            required
                            :invalid="submitted && !accountList.code"
                            fluid
                        />

                        <small
                            v-if="accountList.errors.code"
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
                        @click="listDialog = false"
                    />
                    <Button
                        label="Guardar"
                        icon="pi pi-check"
                        @click="saveAccountingList"
                        :loading="submitted"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteAccountingDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span
                        >Estas seguro que deseas eliminar la lista de
                        contabilidad <b>{{ accountList.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteAccountingDialog = false"
                        severity="secondary"
                        variant="text"
                    />
                    <Button
                        label="Si"
                        icon="pi pi-check"
                        @click="deleteAccountingLists"
                        severity="danger"
                        :loading="submitted"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
