<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Clock, CheckCircle, Bell } from 'lucide-vue-next';

interface DashboardStats {
    overview: {
        new_orders_count: number;
        last_login: string | null;
    };
    commissions: {
        pending: number;
        pending_count: number;
        approved: number;
        approved_count: number;
        paid: number;
        paid_count: number;
    };
    recent_orders: Array<{
        id: number;
        name: string;
        partner_name: string;
        amount_total: number;
        create_date: string;
        state: string;
        status: string;
        commission: number;
        owner: string;
    }>;
}

defineProps<{
    stats: DashboardStats;
    isManager: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Overview of your sales and commissions.
                        <span v-if="stats.overview.new_orders_count > 0" class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            {{ stats.overview.new_orders_count }} new orders
                        </span>
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Commissions</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.commissions.pending_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            commission{{ stats.commissions.pending_count !== 1 ? 's' : '' }} waiting for approval
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CheckCircle class="h-4 w-4 text-emerald-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-emerald-600">{{ stats.commissions.approved_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            commission{{ stats.commissions.approved_count !== 1 ? 's' : '' }} ready for payment
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Paid Total</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.commissions.paid_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            commission{{ stats.commissions.paid_count !== 1 ? 's' : '' }} lifetime earnings
                        </p>
                    </CardContent>
                </Card>
                <Card v-if="stats.overview.new_orders_count > 0" class="bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-blue-900 dark:text-blue-100">New Activity</CardTitle>
                        <Bell class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.overview.new_orders_count }}</div>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80">Orders since last login</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-1 lg:grid-cols-7">
                <!-- Recent Orders -->
                <Card class="lg:col-span-7">
                    <CardHeader>
                        <CardTitle>Recent Orders</CardTitle>
                        <CardDescription>Latest sales orders from Odoo.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Order #</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Owner</TableHead>
                                    <TableHead class="text-right">Status</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="order in stats.recent_orders" :key="order.id">
                                    <TableCell class="font-medium">{{ order.name }}</TableCell>
                                    <TableCell>{{ formatDate(order.create_date) }}</TableCell>
                                    <TableCell>{{ order.partner_name }}</TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-medium text-slate-600">
                                                {{ order.owner.charAt(0) }}
                                            </div>
                                            <span class="text-sm text-muted-foreground">{{ order.owner }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Badge variant="outline" :class="{
                                            'bg-yellow-50 text-yellow-700 border-yellow-200': order.status === 'pending',
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': order.status === 'approved',
                                            'bg-blue-50 text-blue-700 border-blue-200': order.status === 'paid',
                                        }">
                                            {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('sales-orders.show', order.id)">View</Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="stats.recent_orders.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center">
                                        No recent orders found.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
