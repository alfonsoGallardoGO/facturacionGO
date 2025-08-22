import "./bootstrap";
import "../css/app.css";
import "../css/styles.scss";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { PrimeVue } from "@primevue/core";
import Aura from "@primeuix/themes/aura";
import { definePreset } from "@primeuix/themes";
import { ToastService } from "primevue";
import Toast from "primevue/toast";
import Toolbar from "primevue/toolbar";
import Button from "primevue/button";
import FileUpload from "primevue/fileupload";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import InputMask from "primevue/inputmask";
import Select from "primevue/select";
import Password from "primevue/password";
import "primeicons/primeicons.css";
import DataView from "primevue/dataview";
import InputNumber from "primevue/inputnumber";
import { ref, onMounted } from "vue";
import Chart from "primevue/chart";
import Popover from "primevue/popover";
import Chip from "primevue/chip";
import ProgressSpinner from "primevue/progressspinner";
import Card from "primevue/card";
import { Link } from "@inertiajs/vue3";
import Tabs from "primevue/tabs";
import TabList from "primevue/tablist";
import Tab from "primevue/tab";
import { createPinia } from "pinia";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            0: "#ffffff",
            50: "{orange.50}",
            100: "{orange.100}",
            200: "{orange.200}",
            300: "{orange.300}",
            400: "{orange.400}",
            500: "{orange.500}",
            600: "{orange.600}",
            700: "{orange.700}",
            800: "{orange.800}",
            900: "{orange.900}",
            950: "{orange.950}",
        },
        colorScheme: {
            light: {
                surface: {
                    0: "#ffffff",
                    50: "{zinc.50}",
                    100: "{zinc.100}",
                    200: "{zinc.200}",
                    300: "{zinc.300}",
                    400: "{zinc.400}",
                    500: "{zinc.500}",
                    600: "{zinc.600}",
                    700: "{zinc.700}",
                    800: "{zinc.800}",
                    900: "{zinc.900}",
                    950: "{zinc.950}",
                },
            },
            dark: {
                surface: {
                    0: "{stone.50}",
                    50: "{stone.50}",
                    100: "{stone.100}",
                    200: "{stone.200}",
                    300: "{stone.300}",
                    400: "{stone.400}",
                    500: "{stone.500}",
                    600: "{stone.600}",
                    700: "{stone.700}",
                    800: "{stone.800}",
                    900: "#0f1014",
                    950: "{stone.950}",
                },
            },
        },
    },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(ZiggyVue);
        app.use(PrimeVue, {
            theme: {
                preset: MyPreset,
                options: {
                    darkModeSelector: ".mode-dark",
                },
            },
        });
        app.use(ToastService);
        app.component("Toolbar", Toolbar);
        app.component("Button", Button);
        app.component("FileUpload", FileUpload);
        app.component("DataTable", DataTable);
        app.component("Column", Column);
        app.component("IconField", IconField);
        app.component("InputIcon", InputIcon);
        app.component("InputText", InputText);
        app.component("Dialog", Dialog);
        app.component("Tag", Tag);
        app.component("InputMask", InputMask);
        app.component("Password", Password);
        app.component("DataView", DataView);
        app.component("InputNumber", InputNumber);
        app.component("Toast", Toast);
        app.component("Chart", Chart);
        app.component("Popover", Popover);
        app.component("Chip", Chip);
        app.component("ProgressSpinner", ProgressSpinner);
        app.component("Select", Select);
        app.component("Card", Card);
        app.component("Link", Link);
        app.component("Tabs", Tabs);
        app.component("TabList", TabList);
        app.component("Tab", Tab);
        app.use(createPinia());
        app.mount(el);

        if (typeof KTApp !== "undefined" && KTApp.init) {
            KTApp.init();
        }

        if (typeof KTMenu !== "undefined") {
            KTMenu.init();
        }
        if (typeof KTScroll !== "undefined") {
            KTScroll.init();
        }

        if (typeof KTLayoutAside !== "undefined" && KTLayoutAside.init) {
            KTLayoutAside.init();
        }

        if (typeof KTToggle !== "undefined" && KTToggle.init) {
            KTToggle.init();
        }

        if (typeof KTDrawer !== "undefined") {
            KTDrawer.init();
        }

        return app;
    },
    progress: {
        color: "#4B5563",
    },

    onSuccess: () => {
        if (typeof KTApp !== "undefined" && KTApp.init) {
            KTApp.init();
        }

        if (typeof KTMenu !== "undefined") {
            KTMenu.init();
        }
        if (typeof KTScroll !== "undefined") {
            KTScroll.init();
        }

        if (typeof KTLayoutAside !== "undefined" && KTLayoutAside.init) {
            KTLayoutAside.init();
        }

        if (typeof KTToggle !== "undefined" && KTToggle.init) {
            KTToggle.init();
        }

        if (typeof KTDrawer !== "undefined") {
            KTDrawer.init();
        }
    },
    onError: (errors) => {
        if (typeof KTApp !== "undefined" && KTApp.init) {
            KTApp.init();
            console.log(
                "Metronic: KTApp.init() re-ejecutado después de un error de Inertia.",
            );
        }
        console.error("Inertia Error:", errors);
    },
});
