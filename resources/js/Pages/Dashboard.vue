<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { useToastService } from '../Stores/toastService.js';// importar para llamar a los mensajes globales
import Button from 'primevue/button';

const { showSuccess, showError } = useToastService(); // llammar a los 2 mensajes por separado showSuccess(); o showError();

const showCompletedSuccess = async () => {
    showSuccess();
};

const showCompletedError = async () => {
    showError();
};

const props = defineProps({
    portals: Array,
    users: Number,
    user: Object,
});

const currentTime = ref(new Date().toLocaleTimeString());

let intervalId = null;

onMounted(() => {
    intervalId = setInterval(() => {
        currentTime.value = new Date().toLocaleTimeString();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="flex h-1/6">
            <div class="w-1/2 mr-10 h-full card border-none">
                <div class="flex items-center">
                    <img class="w-16 h-16 rounded-full object-cover" :src="$page.props.auth.user?.profile_photo_url"
                        alt="" />
                    <div>
                        <h1 class="text-xl font-normal mb-0 ml-5 font-display">
                            Bienvenid@ {{ $page.props.auth.user?.name }}
                        </h1>
                        <h2 class="text-sm text-gray-600 font-normal ml-5 mt-0 mb-0">
                            <!-- {{ props.user[0] }} -->
                            <!-- <button @click="createProduct">Crear Producto</button>
                            <button @click="deleteProduct">Eliminar Producto</button> -->
                            <div>
                                <Button label="Success" severity="success" @click="showCompletedSuccess" />
                                <Button label="Error" severity="danger" @click="showCompletedError" />
                            </div>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="card mb-0 w-1/4 mr-10 border-none">
                <div class="flex justify-between">
                    <div>
                        <h3 class="block text-lg font-medium mb-4">
                            Usuarios totales
                        </h3>
                        <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">
                            <h4>{{ props.users }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center justify-center bg-cyan-100 dark:bg-cyan-400/10 rounded"
                        style="width: 3.5rem; height: 3.5rem">
                        <i class="pi pi-users text-cyan-500 !text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="card mb-0 w-1/4 border-none">
                <div class="flex justify-between">
                    <div class="text-surface-900 dark:text-surface-0 font-medium text-xl">
                        <h3 class="block text-lg font-medium mb-4">
                            Hora actual
                        </h3>

                        <h4>{{ currentTime }}</h4>
                    </div>
                    <div class="flex items-center justify-center bg-indigo-100 dark:bg-indigo-400/10 rounded mt-2"
                        style="width: 3.5rem; height: 3.5rem">
                        <i class="pi pi-clock text-indigo-500 !text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
