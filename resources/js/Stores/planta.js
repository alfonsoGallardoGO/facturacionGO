import { defineStore } from "pinia";

export const usePlantaStore = defineStore("planta", {
    state: () => ({
        seleccionada: null,
    }),
    actions: {
        seleccionar(planta) {
            this.seleccionada = planta;
        },
        limpiar() {
            this.seleccionada = null;
        },
    },
});
