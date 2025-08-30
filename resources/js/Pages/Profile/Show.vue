<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import Header from "@/Components/Header.vue";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Perfil">
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <Header :title="'Perfil del Proveedor'" />
            <div>
                <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                    <div
                        v-if="$page.props.jetstream.canUpdateProfileInformation"
                    >
                        <UpdateProfileInformationForm
                            :user="$page.props.auth.user"
                        />

                        <SectionBorder />
                    </div>

                    <div v-if="$page.props.jetstream.canUpdatePassword">
                        <UpdatePasswordForm class="sm:mt-0" />

                        <SectionBorder />
                    </div>

                    <div
                        v-if="
                            $page.props.jetstream
                                .canManageTwoFactorAuthentication
                        "
                    >
                        <TwoFactorAuthenticationForm
                            :requires-confirmation="
                                confirmsTwoFactorAuthentication
                            "
                            class="sm:mt-0"
                        />

                        <SectionBorder />
                    </div>

                    <LogoutOtherBrowserSessionsForm
                        :sessions="sessions"
                        class="sm:mt-0"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
