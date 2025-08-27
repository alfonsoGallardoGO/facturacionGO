<script setup>
import { onMounted, ref } from "vue";
import { useLayout } from "./composables/layout";
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { usePlantaStore } from "@/Stores/planta";

const { initTheme } = useLayout();
const plantaStore = usePlantaStore();

const op = ref();
const plantas = ref([]);
const plantasCopy = ref([]);
const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();

const toggle = (event) => {
    op.value.toggle(event);
};

const filterPlantas = (searchTerm) => {
    plantas.value = [...plantasCopy.value];
    if (!searchTerm) {
        plantas.value = [...plantasCopy.value];
        return;
    }

    plantas.value = plantas.value.filter((planta) =>
        planta.code.toLowerCase().includes(searchTerm.toLowerCase()),
    );
};

function seleccionar(planta) {
    plantaStore.seleccionar(planta);
    localStorage.setItem("selectedPlanta", JSON.stringify(planta));
}

onMounted(async () => {
    const plantasDB = await axios.get("/plantas");
    plantas.value = plantasDB.data;
    plantasCopy.value = plantasDB.data;

    plantaStore.seleccionar(JSON.parse(localStorage.getItem("selectedPlanta")));

    // restaurar tema
    initTheme();
});
</script>

<template>
    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button
                class="layout-menu-button layout-topbar-action"
                @click="toggleMenu"
            >
                <i class="pi pi-bars"></i>
            </button>
            <Link href="/" class="layout-topbar-logo">
                <img
                    src="/assets/media/logos/Logo.png"
                    alt="logo"
                    width="30"
                    height="30"
                />
                <span class="">FACTURACION GO</span>
            </Link>
        </div>

        <div class="layout-topbar-actions">
            <Button type="button" icon="pi pi-objects-column" @click="toggle" />

            <Popover ref="op">
                <div class="flex flex-col gap-4 w-96">
                    <div class="h-64 overflow-y-auto">
                        <div class="">
                            <InputText
                                type="text"
                                v-model="value"
                                class="w-full mb-5"
                                size="normal"
                                placeholder="Buscar planta..."
                                @input="filterPlantas(value)"
                            />
                            <ul
                                class="list-none p-0 m-0 grid grid-cols-3 gap-3 text-center items-center justify-center"
                            >
                                <li
                                    v-for="planta in plantas"
                                    :key="planta.id"
                                    :class="
                                        plantaStore.seleccionada?.id ===
                                        planta.id
                                            ? 'bg-cyan-100 dark:bg-cyan-400/10'
                                            : ''
                                    "
                                    class="p-2 hover:bg-cyan-100 dark:hover:bg-cyan-400/10 cursor-pointer flex flex-col items-center"
                                    @click="seleccionar(planta)"
                                >
                                    <div
                                        class="flex items-center justify-center bg-cyan-100 dark:bg-cyan-400/10 rounded"
                                        style="width: 3.5rem; height: 3.5rem"
                                    >
                                        <i
                                            class="pi pi-building text-cyan-500 !text-2xl"
                                        ></i>
                                    </div>
                                    <span class="mt-2 text-sm">{{
                                        planta.code
                                    }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </Popover>

            <div class="layout-config-menu">
                <button
                    type="button"
                    class="layout-topbar-action"
                    @click="toggleDarkMode"
                >
                    <i
                        :class="[
                            'pi',
                            { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme },
                        ]"
                    ></i>
                </button>
            </div>

            <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{
                    selector: '@next',
                    enterFromClass: 'hidden',
                    enterActiveClass: 'animate-scalein',
                    leaveToClass: 'hidden',
                    leaveActiveClass: 'animate-fadeout',
                    hideOnOutsideClick: true,
                }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <Link
                        href="/user/profile"
                        type="button"
                        class="layout-topbar-action"
                    >
                        <i class="pi pi-user"></i>
                        <span>Profile</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
