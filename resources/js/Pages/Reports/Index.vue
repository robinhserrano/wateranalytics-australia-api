<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import MonthlyBarChart from '@/components/charts/MonthlyBarChart.vue';
import { computed, ref } from 'vue';
import { Filter } from 'lucide-vue-next';

type MonthOrder = {
    id: number;
    name: string;
    partner_name: string | null;
    owner: string | null;
    amount_total: number;
    profit: number;
    delivery_status: string | null;
    create_date: string | null;
};

type MonthDetail = {
    month: string;
    label: string;
    total_sales: number;
    total_profit: number;
    active_reps: number;
    orders: MonthOrder[];
};

const props = defineProps<{
    monthly: Array<{ month: string; label: string; total_sales: number; total_profit: number; active_reps: number }>;
    totals: { total_sales: number; total_profit: number };
    users: Array<{ id: number; name: string }>;
    filters: { user_ids: number[] };
    monthDetail: MonthDetail | null;
}>();

const selectedUserIds = ref<number[]>(props.filters.user_ids ?? []);
const isFilterOpen = ref(false);
const isDrilldownOpen = ref(!!props.monthDetail);

const toggleUser = (id: number) => {
    const index = selectedUserIds.value.indexOf(id);
    if (index > -1) {
        selectedUserIds.value.splice(index, 1);
    } else {
        selectedUserIds.value.push(id);
    }
};

const applyFilter = () => {
    router.get(
        route('reports.index'),
        { user_ids: selectedUserIds.value },
        { preserveState: true, replace: true },
    );
    isFilterOpen.value = false;
};

const resetFilter = () => {
    selectedUserIds.value = [];
    applyFilter();
};

const openMonth = (month: string) => {
    router.get(
        route('reports.index'),
        { user_ids: selectedUserIds.value, month },
        {
            preserveState: true,
            replace: true,
            onSuccess: () => {
                isDrilldownOpen.value = true;
            },
        },
    );
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
        maximumFractionDigits: 0,
    }).format(amount);
};

const salesChartData = computed(() =>
    props.monthly.map((m) => ({ month: m.month, label: m.label, value: m.total_sales, active_reps: m.active_reps })),
);
const profitChartData = computed(() =>
    props.monthly.map((m) => ({ month: m.month, label: m.label, value: m.total_profit, active_reps: m.active_reps })),
);
</script>

<template>
    <Head title="Reports" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <h1 class="text-xl md:text-2xl font-bold tracking-tight">Sales Report</h1>

                <Popover v-model:open="isFilterOpen">
                    <PopoverTrigger as-child>
                        <Button variant="outline" class="gap-2">
                            <Filter class="size-4" />
                            Filter by Users
                            <Badge v-if="selectedUserIds.length > 0" variant="secondary" class="ml-1 h-5 px-1.5 text-[10px]">
                                {{ selectedUserIds.length }}
                            </Badge>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-72" align="end">
                        <div class="grid gap-3">
                            <Label class="text-sm font-semibold">Filter by Users</Label>
                            <div class="grid gap-2 max-h-64 overflow-y-auto">
                                <div v-for="u in users" :key="u.id" class="flex items-center space-x-2">
                                    <Checkbox
                                        :id="'user-' + u.id"
                                        :model-value="selectedUserIds.includes(u.id)"
                                        @update:model-value="toggleUser(u.id)"
                                    />
                                    <label :for="'user-' + u.id" class="text-sm cursor-pointer">{{ u.name }}</label>
                                </div>
                                <p v-if="users.length === 0" class="text-sm text-muted-foreground">No users found.</p>
                            </div>
                            <div class="flex justify-between gap-2 pt-2 border-t">
                                <Button variant="ghost" size="sm" @click="resetFilter">Reset</Button>
                                <Button size="sm" @click="applyFilter">Apply</Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Sales (last 12 months)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ formatCurrency(totals.total_sales) }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">Total Profit (last 12 months)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold">{{ formatCurrency(totals.total_profit) }}</p>
                    </CardContent>
                </Card>
            </div>

            <p class="text-xs text-muted-foreground -mt-3">Click a bar to see the month's sales orders.</p>

            <Card>
                <CardContent class="pt-6">
                    <MonthlyBarChart :monthly="salesChartData" title="Total Sales By Month" color="#0891b2" @bar-click="openMonth" />
                </CardContent>
            </Card>

            <Card>
                <CardContent class="pt-6">
                    <MonthlyBarChart :monthly="profitChartData" title="Total Profit By Month" color="#059669" @bar-click="openMonth" />
                </CardContent>
            </Card>
        </div>

        <!-- Month drill-down -->
        <Dialog :open="isDrilldownOpen" @update:open="isDrilldownOpen = $event">
            <DialogContent class="sm:max-w-3xl max-h-[85vh] overflow-y-auto" v-if="monthDetail">
                <DialogHeader>
                    <DialogTitle>{{ monthDetail.label }} Overview</DialogTitle>
                </DialogHeader>

                <div class="grid grid-cols-3 gap-3 mb-4">
                    <Card>
                        <CardContent class="pt-4 pb-3">
                            <p class="text-xs text-muted-foreground">Total Sales</p>
                            <p class="text-lg font-bold">{{ formatCurrency(monthDetail.total_sales) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-3">
                            <p class="text-xs text-muted-foreground">Total Profit</p>
                            <p class="text-lg font-bold">{{ formatCurrency(monthDetail.total_profit) }}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="pt-4 pb-3">
                            <p class="text-xs text-muted-foreground">Active Reps</p>
                            <p class="text-lg font-bold">{{ monthDetail.active_reps }}</p>
                        </CardContent>
                    </Card>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Order #</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Salesperson</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead>Profit</TableHead>
                            <TableHead>Delivery</TableHead>
                            <TableHead class="text-right">&nbsp;</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="order in monthDetail.orders" :key="order.id">
                            <TableCell class="font-medium">{{ order.name }}</TableCell>
                            <TableCell>{{ order.partner_name || '-' }}</TableCell>
                            <TableCell>{{ order.owner || '-' }}</TableCell>
                            <TableCell>{{ formatCurrency(order.amount_total) }}</TableCell>
                            <TableCell :class="order.profit >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                {{ formatCurrency(order.profit) }}
                            </TableCell>
                            <TableCell class="capitalize">{{ order.delivery_status || '-' }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('sales-orders.show', order.id)">View</Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="monthDetail.orders.length === 0">
                            <TableCell :colspan="7" class="h-20 text-center text-muted-foreground">
                                No sales orders this month.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
