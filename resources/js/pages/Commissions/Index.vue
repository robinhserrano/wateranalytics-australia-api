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
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search, DollarSign, TrendingUp, CheckCircle, Clock } from 'lucide-vue-next';

const props = defineProps<{
    commissions: {
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
        status?: string;
        sales_source?: string;
    };
    summary: {
        total_pending: number;
        total_approved: number;
        total_paid: number;
        count_pending: number;
        count_approved: number;
        count_paid: number;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const salesSource = ref(props.filters.sales_source || 'all');

const handleSearch = useDebounceFn((value: string) => {
    updateFilters({ search: value });
}, 300);

const updateFilters = (newFilters: any) => {
    router.get(
        route('commissions.index'),
        {
            search: search.value,
            status: status.value !== 'all' ? status.value : undefined,
            sales_source: salesSource.value !== 'all' ? salesSource.value : undefined,
            ...newFilters,
        },
        { preserveState: true, replace: true }
    );
};

watch(search, (value) => {
    handleSearch(value);
});

watch(status, () => {
    updateFilters({});
});

watch(salesSource, () => {
    updateFilters({});
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

const getStatusBadgeVariant = (status: string) => {
    const variants: Record<string, any> = {
        pending: 'secondary',
        approved: 'default',
        rejected: 'destructive',
        paid: 'outline',
    };
    return variants[status] || 'secondary';
};

const getSalesSourceBadge = (source: string) => {
    return source === 'self_gen' ? 'Self Gen' : 'Company Lead';
};

const getSalesSourceVariant = (source: string) => {
    return source === 'self_gen' ? 'default' : 'secondary';
};
</script>

<template>
    <Head title="Commissions" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Commissions</h1>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_pending) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ summary.count_pending }} commission{{ summary.count_pending !== 1 ? 's' : '' }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_approved) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ summary.count_approved }} commission{{ summary.count_approved !== 1 ? 's' : '' }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Paid</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(summary.total_paid) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ summary.count_paid }} commission{{ summary.count_paid !== 1 ? 's' : '' }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Earnings</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(summary.total_approved + summary.total_paid) }}
                        </div>
                        <p class="text-xs text-muted-foreground">Approved & Paid</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2">
                <div class="relative w-full max-w-sm items-center">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by order or salesperson..."
                        class="pl-10"
                    />
                    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                        <Search class="size-4 text-muted-foreground" />
                    </span>
                </div>

                <Select v-model="status">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="All Statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="approved">Approved</SelectItem>
                        <SelectItem value="rejected">Rejected</SelectItem>
                        <SelectItem value="paid">Paid</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="salesSource">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="All Sources" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Sources</SelectItem>
                        <SelectItem value="self_gen">Self Gen</SelectItem>
                        <SelectItem value="company_lead">Company Lead</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Commissions Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Sales Order</TableHead>
                            <TableHead>Salesperson</TableHead>
                            <TableHead>Source</TableHead>
                            <TableHead>Base</TableHead>
                            <TableHead>Extra</TableHead>
                            <TableHead>Adjustment</TableHead>
                            <TableHead>Final</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="commission in commissions.data"
                            :key="commission.id"
                        >
                            <TableCell class="font-medium">
                                <div>{{ commission.sales_order?.name || 'N/A' }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ commission.sales_order?.partner_name || '' }}
                                </div>
                            </TableCell>
                            <TableCell>{{ commission.user?.name || 'N/A' }}</TableCell>
                            <TableCell>
                                <Badge :variant="getSalesSourceVariant(commission.sales_source)">
                                    {{ getSalesSourceBadge(commission.sales_source) }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ formatCurrency(commission.base_commission) }}</TableCell>
                            <TableCell>{{ formatCurrency(commission.extra_commission) }}</TableCell>
                            <TableCell>
                                <span :class="commission.manual_adjustment !== 0 ? 'font-semibold' : ''">
                                    {{ formatCurrency(commission.manual_adjustment) }}
                                </span>
                            </TableCell>
                            <TableCell class="font-bold">
                                {{ formatCurrency(commission.final_commission) }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusBadgeVariant(commission.status)">
                                    {{ commission.status }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('commissions.show', commission.id)">
                                        View
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="commissions.data.length === 0">
                            <TableCell colspan="9" class="h-24 text-center">
                                No commissions found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between py-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ commissions.from }} to {{ commissions.to }} of {{ commissions.total }} results
                </div>
                <div class="flex items-center space-x-2">
                    <template v-for="(link, index) in commissions.links" :key="index">
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
