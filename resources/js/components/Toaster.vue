<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch, ref, onMounted } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, XCircle, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref<'success' | 'error'>('success');
const timer = ref<any>(null);

const show = (msg: string, t: 'success' | 'error' = 'success') => {
    message.value = msg;
    type.value = t;
    visible.value = true;

    if (timer.value) clearTimeout(timer.value);
    timer.value = setTimeout(() => {
        visible.value = false;
    }, 5000);
};

watch(() => page.props.flash as any, (flash: any) => {
    if (flash?.success) {
        show(flash.success, 'success');
    } else if (flash?.error) {
        show(flash.error, 'error');
    }
}, { deep: true });

onMounted(() => {
    const flash = page.props.flash as any;
    if (flash?.success) {
        show(flash.success as string, 'success');
    } else if (flash?.error) {
        show(flash.error as string, 'error');
    }
});
</script>

<template>
    <div class="fixed bottom-4 right-4 z-[100] w-full max-w-sm transition-all duration-300 ease-in-out" :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'">
        <Alert :variant="type === 'error' ? 'destructive' : 'default'" class="shadow-lg bg-background border-border">
            <CheckCircle2 v-if="type === 'success'" class="h-4 w-4 text-green-600" />
            <XCircle v-else class="h-4 w-4 text-destructive" />
            <AlertTitle class="flex items-center justify-between">
                <span class="capitalize">{{ type }}</span>
                <Button variant="ghost" size="icon" class="h-4 w-4 -mr-2" @click="visible = false">
                    <X class="h-3 w-3" />
                </Button>
            </AlertTitle>
            <AlertDescription>
                {{ message }}
            </AlertDescription>
        </Alert>
    </div>
</template>
