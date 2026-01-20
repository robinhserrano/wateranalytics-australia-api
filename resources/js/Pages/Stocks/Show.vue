<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Package, Warehouse as WarehouseIcon } from 'lucide-vue-next';

defineProps<{
    stock: any;
}>();

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
    <Head :title="stock.display_name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('stocks.index')">
                        <ArrowLeft class="size-4" />
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Package class="size-6" />
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ stock.display_name }}
                    </h1>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Product Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">Odoo ID</div>
                            <div class="text-sm">{{ stock.odoo_id }}</div>
                            
                            <div class="text-sm font-medium text-muted-foreground">Category</div>
                            <div class="text-sm">{{ stock.categ_name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Cost Method</div>
                            <div class="text-sm capitalize">{{ stock.cost_method || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Average Cost</div>
                            <div class="text-sm">{{ formatCurrency(stock.avg_cost) }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Total Value</div>
                            <div class="text-sm font-semibold">{{ formatCurrency(stock.total_value) }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <WarehouseIcon class="size-5" />
                            <CardTitle>Warehouse</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">Name</div>
                            <div class="text-sm">{{ stock.warehouse?.name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Code</div>
                            <div class="text-sm">{{ stock.warehouse?.code || '-' }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Stock Quantities</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">On Hand</div>
                                <div class="text-2xl font-bold">{{ formatQty(stock.qty_available) }}</div>
                            </div>

                            <div class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">Available</div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatQty(stock.free_qty) }}
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">Incoming</div>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatQty(stock.incoming_qty) }}
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">Outgoing</div>
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ formatQty(stock.outgoing_qty) }}
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">Forecast</div>
                                <div class="text-2xl font-bold">{{ formatQty(stock.virtual_available) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
