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
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import { ref, computed, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import {
    Search,
    ChevronLeft,
    ChevronRight,
    Clock,
    PanelLeftClose,
    PanelLeftOpen,
    Warehouse,
    Tag,
} from 'lucide-vue-next';

const props = defineProps<{
    stocks: {
        data: any[];
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
    availableCategories: { id: number; name: string }[];
    filters: {
        search?: string;
        warehouse_id?: string;
        categories?: number[];
    };
    dataSource: 'live' | 'cached';
}>();

// --- Filter State ---
const search        = ref(props.filters.search || '');
const warehouseId   = ref(props.filters.warehouse_id || '0');
const selectedCats  = ref<number[]>(
    (props.filters.categories || []).map(Number).filter(Boolean)
);

// --- Sidebar state ---
const sidebarOpen = ref(true);

// --- Fetch on any filter change ---
const applyFilters = useDebounceFn(() => {
    router.get(
        route('stocks.index'),
        {
            search:       search.value || undefined,
            warehouse_id: warehouseId.value,
            categories:   selectedCats.value.length ? selectedCats.value : undefined,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([search, warehouseId, selectedCats], applyFilters, { deep: true });

function selectWarehouse(id: string) {
    warehouseId.value = id;
}

function toggleCategory(id: number) {
    if (selectedCats.value.includes(id)) {
        selectedCats.value = selectedCats.value.filter(c => c !== id);
    } else {
        selectedCats.value = [...selectedCats.value, id];
    }
}

function getCatName(id: number): string {
    return props.availableCategories.find(c => c.id === id)?.name ?? String(id);
}

// --- Formatting ---
const formatCurrency = (amount: number | null) => {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(amount);
};

const formatQty = (qty: number | null) => {
    if (qty === null || qty === undefined) return '-';
    return new Intl.NumberFormat('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(qty);
};

const selectedWarehouseName = computed(() => {
    if (warehouseId.value === '0') return 'All Warehouses';
    return props.warehouses.find(w => String(w.id) === String(warehouseId.value))?.name ?? 'All Warehouses';
});
</script>

<template>
    <Head title="Product Stocks" />

    <AppLayout>
        <div class="flex h-full flex-1 overflow-hidden">

            <!-- ── Collapsible Sidebar ─────────────────────────────────── -->
            <aside
                :class="[
                    'flex flex-col shrink-0 border-r bg-sidebar transition-all duration-300 overflow-hidden',
                    sidebarOpen ? 'w-56' : 'w-0'
                ]"
            >
                <div class="flex flex-col gap-1 p-3 overflow-y-auto flex-1 min-w-56">

                    <!-- Warehouses section -->
                    <div class="mb-1">
                        <div class="flex items-center gap-1.5 px-1 mb-1.5">
                            <Warehouse class="size-3.5 text-muted-foreground" />
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-muted-foreground">Warehouses</span>
                        </div>

                        <!-- All Warehouses -->
                        <button
                            @click="selectWarehouse('0')"
                            :class="[
                                'w-full text-left px-2 py-1.5 rounded-md text-sm transition-colors',
                                warehouseId === '0'
                                    ? 'bg-primary text-primary-foreground font-semibold'
                                    : 'hover:bg-accent hover:text-accent-foreground text-foreground'
                            ]"
                        >
                            All Warehouses
                        </button>

                        <!-- Individual Warehouses -->
                        <button
                            v-for="wh in warehouses"
                            :key="wh.id"
                            @click="selectWarehouse(String(wh.id))"
                            :class="[
                                'w-full text-left px-2 py-1.5 rounded-md text-sm transition-colors',
                                String(warehouseId) === String(wh.id)
                                    ? 'bg-primary text-primary-foreground font-semibold'
                                    : 'hover:bg-accent hover:text-accent-foreground text-foreground'
                            ]"
                        >
                            {{ wh.name }}
                        </button>
                    </div>

                    <Separator class="my-2" />

                    <!-- Categories section -->
                    <div>
                        <div class="flex items-center gap-1.5 px-1 mb-1.5">
                            <Tag class="size-3.5 text-muted-foreground" />
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-muted-foreground">Category</span>
                        </div>
                        <div
                            v-for="cat in availableCategories"
                            :key="cat.id"
                            class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-accent cursor-pointer transition-colors"
                            @click="toggleCategory(cat.id)"
                        >
                            <Checkbox
                                :id="`cat-${cat.id}`"
                                :checked="selectedCats.includes(cat.id)"
                                @click.stop
                                @update:checked="() => toggleCategory(cat.id)"
                                class="size-3.5"
                            />
                            <label :for="`cat-${cat.id}`" class="text-sm cursor-pointer select-none flex-1">
                                {{ cat.name }}
                            </label>
                        </div>

                        <!-- Clear categories -->
                        <button
                            v-if="selectedCats.length > 0"
                            @click="selectedCats = []"
                            class="mt-2 w-full text-left px-2 py-1 text-xs text-muted-foreground hover:text-destructive transition-colors"
                        >
                            Clear filters
                        </button>
                    </div>
                </div>
            </aside>

            <!-- ── Main Content ──────────────────────────────────────────── -->
            <div class="flex flex-1 flex-col gap-4 p-4 overflow-auto min-w-0">

                <!-- Header row -->
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <!-- Sidebar toggle -->
                        <Button variant="outline" size="icon" class="size-8 shrink-0" @click="sidebarOpen = !sidebarOpen">
                            <PanelLeftClose v-if="sidebarOpen" class="size-4" />
                            <PanelLeftOpen  v-else             class="size-4" />
                        </Button>

                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight">Product Stocks</h1>
                            <span class="text-muted-foreground text-sm font-medium">/ {{ selectedWarehouseName }}</span>

                            <!-- Data source badge -->
                            <Badge
                                v-if="dataSource === 'live'"
                                variant="outline"
                                class="bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 gap-1.5 py-1"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                Live
                            </Badge>
                            <Badge
                                v-else
                                variant="outline"
                                class="bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 gap-1.5 py-1"
                            >
                                <Clock class="size-3" />
                                Cached Fallback
                            </Badge>
                        </div>
                    </div>

                    <!-- Right controls: pagination + search -->
                    <div class="flex items-center gap-3">
                        <!-- Pagination info -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-muted-foreground whitespace-nowrap">
                                {{ stocks.from }}–{{ stocks.to }} / {{ stocks.total }}
                            </span>
                            <div class="flex items-center gap-1">
                                <Button variant="outline" size="icon" class="size-8" :disabled="!stocks.prev_page_url" as-child>
                                    <Link v-if="stocks.prev_page_url" :href="stocks.prev_page_url" preserve-scroll>
                                        <ChevronLeft class="size-4" />
                                    </Link>
                                    <span v-else><ChevronLeft class="size-4" /></span>
                                </Button>
                                <Button variant="outline" size="icon" class="size-8" :disabled="!stocks.next_page_url" as-child>
                                    <Link v-if="stocks.next_page_url" :href="stocks.next_page_url" preserve-scroll>
                                        <ChevronRight class="size-4" />
                                    </Link>
                                    <span v-else><ChevronRight class="size-4" /></span>
                                </Button>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="relative w-56">
                            <Input v-model="search" type="text" placeholder="Search products…" class="pl-9" />
                            <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2.5 pointer-events-none">
                                <Search class="size-4 text-muted-foreground" />
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Active category pills -->
                <div v-if="selectedCats.length > 0" class="flex flex-wrap gap-1.5 -mt-2">
                    <Badge
                        v-for="cat in selectedCats"
                        :key="cat"
                        variant="secondary"
                        class="gap-1 cursor-pointer hover:bg-destructive/10 hover:text-destructive transition-colors"
                        @click="toggleCategory(cat)"
                    >
                        {{ getCatName(cat) }}
                        <span class="ml-0.5 opacity-60">×</span>
                    </Badge>
                </div>

                <!-- Table -->
                <div class="rounded-lg border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50">
                                <TableHead class="font-semibold w-[30%]">Product</TableHead>
                                <TableHead class="font-semibold w-[14%]">Category</TableHead>
                                <TableHead class="font-semibold text-right w-[14%]">On Hand</TableHead>
                                <TableHead class="font-semibold text-right w-[14%]">Free to Use</TableHead>
                                <TableHead class="font-semibold text-right w-[14%]">Incoming</TableHead>
                                <TableHead class="font-semibold text-right w-[14%]">Outgoing</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="stock in stocks.data"
                                :key="stock.id"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <TableCell class="font-medium">
                                    <span class="block" :title="stock.display_name">
                                        {{ stock.display_name }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-muted-foreground text-sm">
                                    {{ stock.categ_name || '-' }}
                                </TableCell>
                                <TableCell class="text-right tabular-nums">
                                    {{ formatQty(stock.qty_available) }}
                                </TableCell>
                                <TableCell class="text-right tabular-nums">
                                    {{ formatQty(stock.free_qty) }}
                                </TableCell>
                                <TableCell class="text-right tabular-nums">
                                    <span :class="Number(stock.incoming_qty) > 0 ? 'text-green-600 font-medium' : ''">
                                        {{ formatQty(stock.incoming_qty) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right tabular-nums">
                                    <span :class="Number(stock.outgoing_qty) > 0 ? 'text-amber-600 font-medium' : ''">
                                        {{ formatQty(stock.outgoing_qty) }}
                                    </span>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="stocks.data.length === 0">
                                <TableCell colspan="6" class="h-32 text-center text-muted-foreground">
                                    No products found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
