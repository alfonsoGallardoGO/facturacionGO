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
                to: "/xml-table",
            },
            {
                label: "Listas de Contabilidad",
                icon: "pi pi-fw pi-book",
                to: "/listas-contabilidad",
            },
            {
                label: "Articulos",
                icon: "pi pi-fw pi-warehouse",
                to: "/articulos",
            },
            {
                label: "Categorias de Factura",
                icon: "pi pi-fw pi-calculator",
                to: "/categoria-facturas",
            },
            {
                label: "Clases de Facturacion",
                icon: "pi pi-fw pi-bookmark",
                to: "/clases-facturacion",
            },
            {
                label: "Empresas",
                icon: "pi pi-fw pi-building-columns",
                to: "/empresas",
            },
            {
                label: "Ubicaciones",
                icon: "pi pi-fw pi-map-marker",
                to: "/ubicaciones",
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
