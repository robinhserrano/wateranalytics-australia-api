<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    salesOrder: any;
}>();

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
</script>

<template>
    <Head :title="`Order ${salesOrder.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('sales-orders.index')">
                        <ArrowLeft class="size-4" />
                    </Link>
                </Button>
                <h1 class="text-2xl font-bold tracking-tight">
                    Order {{ salesOrder.name }}
                </h1>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Date:</span>
                            <span>{{ formatDate(salesOrder.create_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Status:</span>
                            <span class="capitalize">{{ salesOrder.state }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Delivery Status:</span>
                            <span class="capitalize">{{ salesOrder.delivery_status || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Payment Status:</span>
                            <span class="capitalize">{{ salesOrder.x_studio_invoice_payment_status || '-' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Customer</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Name:</span>
                            <span>{{ salesOrder.partner_name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Salesperson:</span>
                            <span>{{ salesOrder.user_name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Team:</span>
                            <span>{{ salesOrder.team_name || '-' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Financials</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <div class="flex items-center justify-between font-medium">
                            <span class="text-muted-foreground">Total:</span>
                            <span>{{ formatCurrency(salesOrder.amount_total) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">To Invoice:</span>
                            <span>{{ formatCurrency(salesOrder.amount_to_invoice) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Product</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead class="text-right">Quantity</TableHead>
                            <TableHead class="text-right">Unit Price</TableHead>
                            <TableHead class="text-right">Subtotal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="line in salesOrder.lines"
                            :key="line.id"
                        >
                            <TableCell class="font-medium">
                                {{ line.product_name }}
                            </TableCell>
                            <TableCell>{{ line.name }}</TableCell>
                            <TableCell class="text-right">
                                {{ line.product_uom_qty }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatCurrency(line.price_unit) }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatCurrency(line.price_subtotal) }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="salesOrder.lines.length === 0">
                            <TableCell colspan="5" class="h-24 text-center">
                                No lines found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
