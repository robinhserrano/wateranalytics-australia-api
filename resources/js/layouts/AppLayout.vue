<script setup lang="ts">
import AppLayout from '@/layouts/App/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import Toaster from '@/components/Toaster.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <Toaster />
</template>
