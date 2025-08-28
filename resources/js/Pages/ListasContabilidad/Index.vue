<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    accountingLists: Array,
});

const selectedAccountingLists = ref([]);
const deleteAccountingDialog = ref(false);

console.log(props.accountingLists);
</script>

<template>
    <AppLayout :title="'Listas de Contabilidad'">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button
                        label="Añadir Lista"
                        icon="pi pi-plus"
                        class="mr-2"
                        @click="openNew"
                    />
                    <Button
                        label="Eliminar Seleccionados"
                        icon="pi pi-trash"
                        severity="danger"
                        variant="outlined"
                        @click="confirmDeleteSelected"
                        :disabled="
                            !selectedAccountingLists ||
                            !selectedAccountingLists.length
                        "
                    />
                </template>
            </Toolbar>

            <Dialog
                v-model:visible="productDialog"
                :style="{ width: '450px' }"
                header="Product Details"
                :modal="true"
            >
                <div class="flex flex-col gap-6">
                    <img
                        v-if="product.image"
                        :src="`https://primefaces.org/cdn/primevue/images/product/${product.image}`"
                        :alt="product.image"
                        class="block m-auto pb-4"
                    />
                    <div>
                        <label for="name" class="block font-bold mb-3"
                            >Name</label
                        >
                        <InputText
                            id="name"
                            v-model.trim="product.name"
                            required="true"
                            autofocus
                            :invalid="submitted && !product.name"
                            fluid
                        />
                        <small
                            v-if="submitted && !product.name"
                            class="text-red-500"
                            >Name is required.</small
                        >
                    </div>
                    <div>
                        <label for="description" class="block font-bold mb-3"
                            >Description</label
                        >
                        <Textarea
                            id="description"
                            v-model="product.description"
                            required="true"
                            rows="3"
                            cols="20"
                            fluid
                        />
                    </div>
                    <div>
                        <label
                            for="inventoryStatus"
                            class="block font-bold mb-3"
                            >Inventory Status</label
                        >
                        <Select
                            id="inventoryStatus"
                            v-model="product.inventoryStatus"
                            :options="statuses"
                            optionLabel="label"
                            placeholder="Select a Status"
                            fluid
                        ></Select>
                    </div>

                    <div>
                        <span class="block font-bold mb-4">Category</span>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="flex items-center gap-2 col-span-6">
                                <RadioButton
                                    id="category1"
                                    v-model="product.category"
                                    name="category"
                                    value="Accessories"
                                />
                                <label for="category1">Accessories</label>
                            </div>
                            <div class="flex items-center gap-2 col-span-6">
                                <RadioButton
                                    id="category2"
                                    v-model="product.category"
                                    name="category"
                                    value="Clothing"
                                />
                                <label for="category2">Clothing</label>
                            </div>
                            <div class="flex items-center gap-2 col-span-6">
                                <RadioButton
                                    id="category3"
                                    v-model="product.category"
                                    name="category"
                                    value="Electronics"
                                />
                                <label for="category3">Electronics</label>
                            </div>
                            <div class="flex items-center gap-2 col-span-6">
                                <RadioButton
                                    id="category4"
                                    v-model="product.category"
                                    name="category"
                                    value="Fitness"
                                />
                                <label for="category4">Fitness</label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6">
                            <label for="price" class="block font-bold mb-3"
                                >Price</label
                            >
                            <InputNumber
                                id="price"
                                v-model="product.price"
                                mode="currency"
                                currency="USD"
                                locale="en-US"
                                fluid
                            />
                        </div>
                        <div class="col-span-6">
                            <label for="quantity" class="block font-bold mb-3"
                                >Quantity</label
                            >
                            <InputNumber
                                id="quantity"
                                v-model="product.quantity"
                                integeronly
                                fluid
                            />
                        </div>
                    </div>
                </div>

                <template #footer>
                    <Button
                        label="Cancel"
                        icon="pi pi-times"
                        text
                        @click="hideDialog"
                    />
                    <Button
                        label="Save"
                        icon="pi pi-check"
                        @click="saveProduct"
                    />
                </template>
            </Dialog>

            <Dialog
                v-model:visible="deleteProductDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="selectedAccountingLists.length === 0"
                        >Estas seguro que deseas eliminar la lista de
                        contabilidad <b>{{ product.name }}</b
                        >?</span
                    >

                    <span v-if="selectedAccountingLists.length > 0"
                        >Estas seguro que deseas eliminar estas listas de
                        contabilidad?</span
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
                        label="Yes"
                        icon="pi pi-check"
                        @click="deleteProduct"
                        severity="danger"
                    />
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
