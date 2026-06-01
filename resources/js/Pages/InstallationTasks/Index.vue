<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Search, Calendar as CalendarIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
    tasks: {
        data: any[];
        links: any[];
        meta: any;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');

const handleSearch = useDebounceFn((value: string) => {
    router.get(
        route('installation-tasks.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => {
    handleSearch(value);
});

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-AU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Installation Tasks" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 relative">
            <div class="flex items-center justify-between gap-2 flex-wrap pb-3 pt-1 border-b">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight">Installation Tasks</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Sales Orders scheduled for installation.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-full max-w-xs items-center">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search orders..."
                            class="pl-10"
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Est. Install Date</TableHead>
                            <TableHead>Order #</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Salesperson</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="tasks.data.length === 0">
                            <TableCell colspan="4" class="text-center py-8 text-muted-foreground">
                                No installation tasks found.
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-for="task in tasks.data"
                            :key="task.id"
                            class="cursor-pointer hover:bg-muted/50 transition-colors"
                            @click="router.visit(route('installation-tasks.show', task.id))"
                        >
                            <TableCell class="font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <CalendarIcon class="size-3.5" />
                                    {{ formatDate(task.installation_date) }}
                                </div>
                            </TableCell>
                            <TableCell class="font-medium">
                                <Link :href="route('sales-orders.show', task.id)" class="hover:underline">
                                    {{ task.name }}
                                </Link>
                            </TableCell>
                            <TableCell>{{ task.partner_name }}</TableCell>
                            <TableCell>{{ task.user_name || '-' }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            
            <div class="flex items-center justify-between mt-2 text-sm text-muted-foreground">
                <div>
                    Showing {{ tasks.from || 0 }} to {{ tasks.to || 0 }} of {{ tasks.total }} results
                </div>
            </div>
        </div>
    </AppLayout>
</template>
