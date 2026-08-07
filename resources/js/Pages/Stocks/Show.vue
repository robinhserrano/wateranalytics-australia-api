<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Package, Warehouse as WarehouseIcon, Clock } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

defineProps<{
    stock: any;
    dataSource: 'live' | 'cached';
}>();

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
                    
                    <Badge v-if="dataSource === 'live'" variant="outline" class="bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 gap-1.5 py-1 ml-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Live
                    </Badge>
                    <Badge v-else variant="outline" class="bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 gap-1.5 py-1 ml-2">
                        <Clock class="size-3" />
                        Cached Fallback
                    </Badge>
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
