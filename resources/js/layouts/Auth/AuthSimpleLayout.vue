<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="home()" class="flex flex-col items-center gap-2 font-medium">
                        <div class="size-12 rounded-2xl bg-white overflow-hidden relative border border-gray-100">
                            <AppLogoIcon class="w-full h-full object-cover" />
                        </div>
                        <span class="sr-only">Water Analytics Australia</span>
                    </Link>
                    <div class="space-y-0.5 text-center">
                        <h1 class="text-[28px] font-bold text-[#171717] leading-tight">
                            Water Analytics Australia
                        </h1>
                        <p class="text-center text-[10px] font-bold uppercase tracking-[0.2em] text-[#525252]">
                            Commission Management System
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
