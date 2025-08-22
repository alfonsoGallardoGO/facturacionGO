<script setup>
import { ref } from "vue";

import AppMenuItem from "./AppMenuItem.vue";

const model = ref([
    {
        label: "Inicio",
        items: [
            {
                label: "Pagina de inicio",
                icon: "pi pi-fw pi-home",
                to: "/dashboard",
            },
        ],
    },
    {
        label: "Facturacion",
        items: [
            {
                label: "XML",
                icon: "pi pi-fw pi-file",
            },
        ],
    },
    // {
    //     label: "Portales",
    // },
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item
                v-if="!item.separator"
                :item="item"
                :index="i"
            ></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
        <!-- <a
            class="layout-menuitem-text"
            target="_blank"
            href="https://nominas.grupo-ortiz.site/"
        >
            <i class="pi pi-address-book mr-2"></i>
            <span class="layout-menuitem-text">Nominas</span>
        </a> -->
    </ul>
</template>

<style lang="scss" scoped></style>
