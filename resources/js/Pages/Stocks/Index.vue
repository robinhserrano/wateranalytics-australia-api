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
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    stocks: {
        data: any[];
        links: any[];
        meta: any;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    warehouses: any[];
    filters: {
        search?: string;
        warehouse_id?: string;
    };
}>();

const search = ref(props.filters.search || '');
const warehouseId = ref(props.filters.warehouse_id || '0');

const handleSearch = useDebounceFn(() => {
    router.get(
        route('stocks.index'),
        {
            search: search.value,
            warehouse_id: warehouseId.value
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([search, warehouseId], () => {
    handleSearch();
});

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const formatQty = (qty: number | null) => {
    if (qty === null) return '-';
    return new Intl.NumberFormat('en-AU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(qty);
};
</script>

<template>

    <Head title="Product Stocks" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Product Stocks</h1>
                <div class="flex items-center gap-4">
                    <!-- Simplified Pagination -->
                    <div class="flex items-center gap-4">
                        <div class="text-sm font-medium text-muted-foreground whitespace-nowrap">
                            {{ stocks.from }} - {{ stocks.to }} / {{ stocks.total }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Button variant="outline" size="icon" class="size-8" :disabled="!stocks.prev_page_url"
                                as-child>
                                <Link v-if="stocks.prev_page_url" :href="stocks.prev_page_url" preserve-scroll>
                                    <ChevronLeft class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronLeft class="size-4" />
                                </span>
                            </Button>
                            <Button variant="outline" size="icon" class="size-8" :disabled="!stocks.next_page_url"
                                as-child>
                                <Link v-if="stocks.next_page_url" :href="stocks.next_page_url" preserve-scroll>
                                    <ChevronRight class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronRight class="size-4" />
                                </span>
                            </Button>
                        </div>
                    </div>

                    <Select :model-value="warehouseId" @update:model-value="(value) => warehouseId = value as string">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue placeholder="All Warehouses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">All Warehouses</SelectItem>
                            <SelectItem v-for="warehouse in warehouses" :key="warehouse.id"
                                :value="warehouse.id.toString()">
                                {{ warehouse.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="relative w-full max-w-sm items-center">
                        <Input v-model="search" type="text" placeholder="Search products..." class="pl-10" />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Product</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Warehouse</TableHead>
                            <TableHead class="text-right">On Hand</TableHead>
                            <TableHead class="text-right">Available</TableHead>
                            <TableHead class="text-right">Incoming</TableHead>
                            <TableHead class="text-right">Outgoing</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="stock in stocks.data" :key="stock.id">
                            <TableCell class="font-medium">
                                {{ stock.display_name }}
                            </TableCell>
                            <TableCell>{{ stock.categ_name || '-' }}</TableCell>
                            <TableCell>{{ stock.warehouse?.name || '-' }}</TableCell>
                            <TableCell class="text-right">{{ formatQty(stock.qty_available) }}</TableCell>
                            <TableCell class="text-right">{{ formatQty(stock.free_qty) }}</TableCell>
                            <TableCell class="text-right">{{ formatQty(stock.incoming_qty) }}</TableCell>
                            <TableCell class="text-right">{{ formatQty(stock.outgoing_qty) }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('stocks.show', stock.id)">
                                        View
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="stocks.data.length === 0">
                            <TableCell colspan="8" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

        </div>
    </AppLayout>
</template>
