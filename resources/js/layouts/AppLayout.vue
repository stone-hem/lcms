<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from 'vue-sonner';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Toaster  />

        <slot />
    </AppLayout>
</template>

<style scoped>
/* Alternative approach: Use a scoped style */
:deep(.sonner-toast) {
    z-index: 9999 !important;
}
</style>
