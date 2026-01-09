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
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search } from 'lucide-vue-next';

const props = defineProps<{
    salesOrders: {
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
    filters: {
        search?: string;
    };
    viewScope?: string;
}>();

const search = ref(props.filters.search || '');

const handleSearch = useDebounceFn((value: string) => {
    router.get(
        route('sales-orders.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => {
    handleSearch(value);
});

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-AU');
};

const formatSource = (source: string | null) => {
    if (!source) return '-';
    return source === 'self_gen' ? 'Self Gen' : 'Company Lead';
};

const formatBoolean = (val: any) => {
    return val ? 'Yes' : 'No';
};

const getDeliveryStatusColor = (status: string | null) => {
    if (!status) return 'text-muted-foreground';
    const s = status.toLowerCase();
    if (s.includes('full') || s.includes('done')) return 'text-green-600';
    if (s.includes('cancel')) return 'text-red-600';
    if (s.includes('ready') || s.includes('process')) return 'text-blue-600';
    return 'text-amber-600';
};
</script>

<template>
    <Head title="Sales Orders" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Sales Orders</h1>
                    <p v-if="viewScope" class="text-sm text-muted-foreground mt-1">
                        Viewing: {{ viewScope }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-full max-w-sm items-center">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="pl-10"
                        />
                        <span
                            class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
                        >
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Order #</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Salesperson</TableHead>
                            <TableHead>Sales Source</TableHead>
                            <TableHead class="text-center">Comm. Paid</TableHead>
                            <TableHead class="text-center">Confirmed By Manager</TableHead>
                            <TableHead class="text-center">Entered to Odoo</TableHead>
                            <TableHead>Delivery Status</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead>Final Commission</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="order in salesOrders.data"
                            :key="order.id"
                        >
                            <TableCell class="font-medium">
                                {{ order.name }}
                            </TableCell>
                            <TableCell>{{ formatDate(order.create_date) }}</TableCell>
                            <TableCell>{{ order.partner_name }}</TableCell>
                            <TableCell>{{ order.user_name }}</TableCell>
                            <TableCell>
                                <Badge v-if="order.commission_calculation" variant="outline" class="whitespace-nowrap">
                                    {{ formatSource(order.commission_calculation.sales_source) }}
                                </Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-center">
                                    <Checkbox :model-value="!!order.x_studio_commission_paid" disabled />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-center">
                                    <Checkbox v-if="order.commission_calculation" :model-value="!!order.commission_calculation.confirmed_by_manager" disabled />
                                    <span v-else class="text-muted-foreground">-</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-center">
                                    <Checkbox v-if="order.commission_calculation" :model-value="!!order.commission_calculation.entered_to_odoo" disabled />
                                    <span v-else class="text-muted-foreground">-</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div :class="['text-xs capitalize', getDeliveryStatusColor(order.delivery_status)]">
                                    {{ order.delivery_status || '-' }}
                                </div>
                            </TableCell>
                            <TableCell>{{ formatCurrency(order.amount_total) }}</TableCell>
                            <TableCell>
                                <span 
                                    v-if="order.commission_calculation" 
                                    class="font-medium"
                                    :class="order.commission_calculation.final_commission > 0 ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ formatCurrency(order.commission_calculation.final_commission) }}
                                </span>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>
                                <div class="capitalize">{{ order.state }}</div>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('sales-orders.show', order.id)">
                                        View
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="salesOrders.data.length === 0">
                            <TableCell colspan="13" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex items-center justify-between py-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ salesOrders.from }} to {{ salesOrders.to }} of {{ salesOrders.total }} results
                </div>
                <div class="flex items-center space-x-2">
                    <template v-for="(link, index) in salesOrders.links" :key="index">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            as-child
                        >
                            <Link :href="link.url" preserve-scroll>
                                <span v-html="link.label"></span>
                            </Link>
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            disabled
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
