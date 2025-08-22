import { useToast } from "primevue/usetoast";

export function useToastService() {
    const toast = useToast();

    const showSuccess = (message = 'Proceso completado correctamente.') => {
        toast.add({ severity: 'success', summary: 'Éxito', detail:'El proceso se realizo correctamente', life: 3000 });
    };

    const showError = (message = 'El proceso tuvo un error.') => {
        toast.add({ severity: 'error', summary: 'Error', detail:'Hubo en error en el proceso', life: 3000 });
    };

    return {
        showSuccess,
        showError
    };
}