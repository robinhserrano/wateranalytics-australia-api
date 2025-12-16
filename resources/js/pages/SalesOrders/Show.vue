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
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Edit } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose,
} from '@/components/ui/dialog';
import { ref, watch } from 'vue';

const props = defineProps<{
    salesOrder: any;
    calculationError?: string | null;
}>();

// Initialize manual adjustment ref
const manualAdjustment = ref(props.salesOrder.commission_calculation?.manual_adjustment || 0);
const isDialogOpen = ref(false);

// Update ref if prop changes (e.g. after recalculation)
watch(() => props.salesOrder.commission_calculation?.manual_adjustment, (newVal) => {
    manualAdjustment.value = newVal || 0;
});

const applyAdjustment = () => {
    // Prevent unnecessary calls
    if (manualAdjustment.value == props.salesOrder.commission_calculation?.manual_adjustment) {
        isDialogOpen.value = false;
        return;
    }
    
    router.post(route('commissions.adjust', props.salesOrder.commission_calculation.id), {
        adjustment_amount: manualAdjustment.value,
        reason: 'Manual Adjustment via UI' 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isDialogOpen.value = false;
        }
    });
};

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
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Payment Type:</span>
                            <span class="capitalize">{{ salesOrder.x_studio_payment_type }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Sales Source:</span>
                            <span class="capitalize">{{ salesOrder.x_studio_sales_source }}</span>
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
            
            <div v-if="calculationError" class="p-4 border border-amber-200 bg-amber-50 dark:bg-amber-950/20 dark:border-amber-800 rounded-md text-amber-800 dark:text-amber-200 text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 18h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                <span><strong>Commission Calculation Issue:</strong> {{ calculationError }}</span>
            </div>

            <Card v-if="salesOrder.commission_calculation" class="border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-950/20">
                <CardHeader>
                    <CardTitle class="flex justify-between items-center">
                        <span>Commission Breakdown</span>
                        <div class="flex items-center gap-2">
                             <Button 
                                variant="outline" 
                                size="sm" 
                                @click="router.post(route('commissions.recalculate', salesOrder.commission_calculation.id))"
                                class="h-8"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw mr-2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                                Recalculate
                            </Button>
                            <span class="text-sm font-normal px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                {{ salesOrder.commission_calculation.status }}
                            </span>
                        </div>
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-6 md:grid-cols-2">

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-semibold text-sm text-muted-foreground uppercase tracking-wider">Configuration</h4>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Assigned User:</span>
                                <span class="font-medium">{{ salesOrder.commission_calculation.user?.name || 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Source:</span>
                                <span class="font-medium capitalize">{{ salesOrder.commission_calculation.sales_source === 'self_gen' ? 'Self Generated' : 'Company Lead' }}</span>
                            </div>
                             <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Commission Split:</span>
                                <span class="font-medium">{{ salesOrder.commission_calculation.calculation_metadata?.commission_split || 0 }}%</span>
                            </div>
                        </div>

                         <div class="space-y-2">
                            <h4 class="font-semibold text-sm text-muted-foreground uppercase tracking-wider">Bases</h4>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Company Lead Base:</span>
                                <span class="font-medium">{{ formatCurrency(salesOrder.commission_calculation.calculation_metadata?.company_lead_base) }}</span>
                            </div>
                             <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Self Gen Base:</span>
                                <span class="font-medium">{{ formatCurrency(salesOrder.commission_calculation.calculation_metadata?.self_gen_base) }}</span>
                            </div>
                        </div>
                    </div>


                    <div class="space-y-2">
                        <h4 class="font-semibold text-sm text-muted-foreground uppercase tracking-wider">Calculation</h4>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Selling Price:</span>
                            <span>{{ formatCurrency(salesOrder.commission_calculation.selling_price) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600 dark:text-red-400">
                             <span class="text-muted-foreground text-red-600/70 dark:text-red-400/70">Less: Additional Cost:</span>
                            <span>-{{ formatCurrency(salesOrder.commission_calculation.additional_cost) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600 dark:text-red-400">
                            <span class="text-muted-foreground text-red-600/70 dark:text-red-400/70">Less: Landing Price:</span>
                            <span>-{{ formatCurrency(salesOrder.commission_calculation.landing_price) }}</span>
                        </div>
                        
                        <div class="border-t pt-2 flex justify-between font-bold">
                            <span>Profit:</span>
                            <span :class="salesOrder.commission_calculation.profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                {{ formatCurrency(salesOrder.commission_calculation.profit) }}
                            </span>
                        </div>

                        <div class="pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Base Commission:</span>
                                <span>{{ formatCurrency(salesOrder.commission_calculation.base_commission) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Extra Commission:</span>
                                <span>{{ formatCurrency(salesOrder.commission_calculation.extra_commission) }}</span>
                            </div>
                            
                            <!-- Manual Addition/Deduction -->
                            <div class="flex items-center justify-between text-sm py-1">
                                <span class="text-muted-foreground">Manual Addition/Deduction:</span>
                                <div class="flex items-center gap-2">
                                    <span :class="salesOrder.commission_calculation.manual_adjustment != 0 ? 'font-medium' : 'text-muted-foreground'">
                                        {{ formatCurrency(salesOrder.commission_calculation.manual_adjustment) }}
                                    </span>
                                    
                                    <Dialog v-model:open="isDialogOpen">
                                        <DialogTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-6 w-6">
                                                <Edit class="h-3 w-3" />
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>Manual Adjustment</DialogTitle>
                                                <DialogDescription>
                                                    Enter a positive or negative amount to adjust the final commission.
                                                </DialogDescription>
                                            </DialogHeader>
                                            <div class="grid gap-4 py-4">
                                                <div class="grid grid-cols-4 items-center gap-4">
                                                    <span class="text-right text-sm font-medium">Amount</span>
                                                    <Input
                                                        type="text"
                                                        inputmode="decimal"
                                                        v-model="manualAdjustment"
                                                        class="col-span-3"
                                                        placeholder="0.00"
                                                    />
                                                </div>
                                            </div>
                                            <DialogFooter>
                                                <DialogClose as-child>
                                                    <Button variant="outline">Cancel</Button>
                                                </DialogClose>
                                                <Button @click="applyAdjustment">Save Changes</Button>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </div>

                            <div class="border-t pt-2 flex justify-between font-bold text-lg">
                                <span>Final Commission:</span>
                                <span class="text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(salesOrder.commission_calculation.final_commission) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

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
