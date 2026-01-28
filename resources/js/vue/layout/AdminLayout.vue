<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import AdminHeader from '../components/admin/AdminHeader.vue';

const toast = useToast();
const page = usePage();

watch(() => page.props.errors, (errors) => {
    if (errors && Object.keys(errors).length > 0) {
        Object.values(errors).forEach(error => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: error, life: 5000 });
        });
    }
}, { immediate: true, deep: true });

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: flash.success, life: 3000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: flash.error, life: 5000 });
    }
    if (flash?.warning) {
        toast.add({ severity: 'warn', summary: 'Attention', detail: flash.warning, life: 4000 });
    }
    if (flash?.info) {
        toast.add({ severity: 'info', summary: 'Information', detail: flash.info, life: 3000 });
    }
    if (flash?.Bonjour) {
        toast.add({ severity: 'success', summary: 'Bonjour', detail: flash.Bonjour, life: 4000 });
    }
}, { deep: true });
</script>

<template>
  <div class="admin-layout min-h-screen bg-white">
    <Toast />
    <AdminHeader />
    <main class="page-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.page-content {
  padding: 0;
  padding:1vh 2vw;
}
</style>
