<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
import { Textarea } from '@/components/ui/textarea';
import { ref, watch, computed } from 'vue';
import { CheckCircle2, XCircle, AlertCircle, Send, User, MapPin, Info } from 'lucide-vue-next';

const props = defineProps<{
    salesOrder: any;
    calculationError?: string | null;
    canViewInstaller?: boolean;
}>();

// Initialize manual adjustment ref
const manualAdjustment = ref(props.salesOrder.commission_calculation?.manual_adjustment || 0);
const isAdjustmentDialogOpen = ref(false);
const isConfirmDialogOpen = ref(false);
const isRejectDialogOpen = ref(false);
const isOdooSyncDialogOpen = ref(false);
const isResetConfirmDialogOpen = ref(false);
const isResetOdooSyncDialogOpen = ref(false);
const rejectionReason = ref('');

const page = usePage<any>();
const isAdmin = computed(() => {
    return (page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Admin');
});

const getLatestLog = (action: string) => {
    return props.salesOrder.commission_calculation?.approvals
        ?.filter((log: any) => log.action === action)
        .sort((a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())[0];
};

// Update ref if prop changes (e.g. after recalculation)
watch(() => props.salesOrder.commission_calculation?.manual_adjustment, (newVal) => {
    manualAdjustment.value = newVal || 0;
});

const applyAdjustment = () => {
    // Prevent unnecessary calls
    if (manualAdjustment.value == props.salesOrder.commission_calculation?.manual_adjustment) {
        isAdjustmentDialogOpen.value = false;
        return;
    }
    
    router.post(route('commissions.adjust', props.salesOrder.commission_calculation.id), {
        adjustment_amount: manualAdjustment.value,
        reason: 'Manual Adjustment via UI' 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isAdjustmentDialogOpen.value = false;
        }
    });
};

const confirmBreakdown = () => {
    router.post(route('commissions.confirm', props.salesOrder.commission_calculation.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isConfirmDialogOpen.value = false;
        }
    });
};

const rejectBreakdown = () => {
    if (!rejectionReason.value || rejectionReason.value.length < 10) {
        alert('Please provide a reason (minimum 10 characters)');
        return;
    }

    router.post(route('commissions.reject', props.salesOrder.commission_calculation.id), {
        reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isRejectDialogOpen.value = false;
            rejectionReason.value = '';
        }
    });
};

const markAsEnteredToOdoo = () => {
    router.post(route('commissions.mark-odoo', props.salesOrder.commission_calculation.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isOdooSyncDialogOpen.value = false;
        }
    });
};

const resetConfirm = () => {
    router.post(route('commissions.reset-confirm', props.salesOrder.commission_calculation.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isResetConfirmDialogOpen.value = false;
        }
    });
};

const resetOdooSync = () => {
    router.post(route('commissions.reset-odoo', props.salesOrder.commission_calculation.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isResetOdooSyncDialogOpen.value = false;
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

            <div class="grid gap-4 md:grid-cols-2">
                <Card class="flex flex-col">
                    <CardContent class="grid gap-4 pt-6">
                        <div class="flex items-center gap-2 text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                            <Info class="size-4" />
                            ORDER DETAILS
                        </div>
                        
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Date</span>
                                <span class="font-bold">{{ formatDate(salesOrder.create_date) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Status</span>
                                <span class="px-3 py-1 bg-emerald-300 text-emerald-900 rounded font-semibold text-xs capitalize">{{ salesOrder.state }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Delivery Status</span>
                                <span class="font-bold capitalize">{{ salesOrder.delivery_status || '-' }}</span>
                            </div>
                            <div class="flex items-start justify-between text-sm gap-4">
                                <span class="text-muted-foreground whitespace-nowrap">Payment Status</span>
                                <span class="font-bold capitalize text-right">{{ salesOrder.x_studio_payment_type || salesOrder.x_studio_invoice_payment_status || '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">EST Install Date</span>
                                <span :class="salesOrder.installation_date ? 'font-bold' : 'italic text-muted-foreground'">
                                    {{ salesOrder.installation_date ? formatDate(salesOrder.installation_date) : 'Not Set' }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t my-1"></div>

                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between font-bold">
                                <span class="text-muted-foreground font-normal">Amount Total:</span>
                                <span>{{ formatCurrency(salesOrder.amount_total) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">To Invoice:</span>
                                <span class="font-medium">{{ formatCurrency(salesOrder.amount_to_invoice) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="flex flex-col">
                    <CardContent class="grid gap-4 pt-6">
                        <div class="flex items-center gap-2 text-sm font-semibold tracking-wider text-muted-foreground uppercase">
                            <User class="size-4" />
                            CUSTOMER INFO
                        </div>
                        
                        <div>
                            <h3 class="text-xl font-bold">{{ salesOrder.partner_name }}</h3>
                            <a v-if="salesOrder.partner?.phone" :href="'tel:' + salesOrder.partner.phone" class="text-blue-600 font-medium hover:underline block mt-0.5">
                                {{ salesOrder.partner.phone }}
                            </a>
                        </div>
                        
                        <div v-if="salesOrder.partner?.contact_address_complete" class="flex gap-2 text-muted-foreground mt-1">
                            <MapPin class="size-4 shrink-0 mt-0.5 flex-none" />
                            <p class="text-sm">
                                {{ salesOrder.partner.contact_address_complete }}
                            </p>
                        </div>
                        
                        <div class="border-t my-1"></div>
                        
                        <div class="space-y-1.5 text-sm">
                            <div>
                                <span class="text-muted-foreground">Salesperson:</span>
                                <span class="font-medium ml-2">{{ salesOrder.user_name || 'N/A' }}</span>
                            </div>
                            <div v-if="canViewInstaller">
                                <span class="text-muted-foreground">Installer:</span>
                                <span class="font-medium ml-2">{{ salesOrder.installer_name || 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Sales Source:</span>
                                <span class="font-medium ml-2 capitalize">{{ salesOrder.x_studio_sales_source || 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Sales Manager:</span>
                                <span class="font-medium ml-2">{{ salesOrder.commissionCalculation?.sales_manager?.name || 'Not assigned' }}</span>
                            </div>
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
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-normal px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 capitalize">
                                    {{ salesOrder.commission_calculation.status }}
                                </span>
                                <div v-if="salesOrder.commission_calculation.status === 'rejected' && getLatestLog('rejected')" class="text-[10px] text-red-600 mt-1">
                                    Rejected by {{ getLatestLog('rejected').approver?.name }} on {{ formatDate(getLatestLog('rejected').created_at) }}
                                </div>
                            </div>
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
                                    
                                    <Dialog v-model:open="isAdjustmentDialogOpen">
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

                            <!-- Action Buttons -->
                            <div class="pt-6 grid grid-cols-2 gap-2 border-t mt-4 pt-4">
                                <!-- Manager Confirmation -->
                                <div class="col-span-2 sm:col-span-1">
                                    <template v-if="!salesOrder.commission_calculation.confirmed_by_manager">
                                        <div class="flex gap-2">
                                            <Dialog v-model:open="isConfirmDialogOpen">
                                                <DialogTrigger as-child>
                                                    <Button variant="default" size="sm" class="flex-1 bg-green-600 hover:bg-green-700">
                                                        <CheckCircle2 class="size-4 mr-2" />
                                                        Confirm Breakdown
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Confirm Commission Breakdown</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to confirm this breakdown? This will signal to accounts that the commission is ready for processing.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button @click="confirmBreakdown" class="bg-green-600 hover:bg-green-700">Confirm</Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>

                                            <Dialog v-model:open="isRejectDialogOpen">
                                                <DialogTrigger as-child>
                                                    <Button variant="outline" size="sm" class="flex-1 text-red-600 border-red-200 hover:bg-red-50">
                                                        <XCircle class="size-4 mr-2" />
                                                        Reject
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Reject Commission Breakdown</DialogTitle>
                                                        <DialogDescription>
                                                            Please provide a reason for rejecting this breakdown. This will notify the salesperson.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <div class="py-4">
                                                        <Textarea 
                                                            v-model="rejectionReason" 
                                                            placeholder="Reason for rejection (min 10 characters)..."
                                                            class="min-h-[100px]"
                                                        />
                                                    </div>
                                                    <DialogFooter>
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button 
                                                            variant="destructive" 
                                                            @click="rejectBreakdown"
                                                            :disabled="!rejectionReason || rejectionReason.length < 10"
                                                        >
                                                            Reject Breakdown
                                                        </Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </template>
                                    <div v-else class="space-y-2">
                                        <div class="flex items-center justify-between p-2 bg-green-50 rounded-md border border-green-100 dark:bg-green-950/20 dark:border-green-800/30">
                                            <div class="flex items-center gap-2 text-green-600 font-medium text-sm">
                                                <CheckCircle2 class="size-4" />
                                                <span>Confirmed by Manager</span>
                                            </div>
                                            
                                            <Dialog v-if="isAdmin" v-model:open="isResetConfirmDialogOpen">
                                                <DialogTrigger as-child>
                                                    <Button variant="ghost" size="sm" class="h-7 text-xs text-muted-foreground hover:text-red-600">Reset</Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Reset Manager Confirmation</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to reset this confirmation? This will set the status back to "Pending" and allow managers to re-confirm or reject.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button variant="destructive" @click="resetConfirm">Reset Confirmation</Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                        
                                        <div v-if="getLatestLog('confirmed')" class="text-[10px] text-muted-foreground pl-1">
                                            Last confirmed by {{ getLatestLog('confirmed').approver?.name }} on {{ formatDate(getLatestLog('confirmed').created_at) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Odoo Sync -->
                                <div class="col-span-2 sm:col-span-1">
                                    <template v-if="!salesOrder.commission_calculation.entered_to_odoo">
                                        <Dialog v-model:open="isOdooSyncDialogOpen">
                                            <DialogTrigger as-child>
                                                <Button 
                                                    variant="outline" 
                                                    size="sm" 
                                                    class="w-full"
                                                >
                                                    <Send class="size-4 mr-2" />
                                                    Mark as Entered to Odoo
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Confirm Odoo Entry</DialogTitle>
                                                    <DialogDescription>
                                                        Confirm that this commission has been successfully entered into the Odoo system.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button variant="outline">Cancel</Button>
                                                    </DialogClose>
                                                    <Button @click="markAsEnteredToOdoo">Proceed</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </template>
                                    <div v-else class="space-y-2">
                                        <div class="flex items-center justify-between p-2 bg-blue-50 rounded-md border border-blue-100 dark:bg-blue-950/20 dark:border-blue-800/30">
                                            <div class="flex items-center gap-2 text-blue-600 font-medium text-sm">
                                                <CheckCircle2 class="size-4" />
                                                <span>Entered to Odoo</span>
                                            </div>
                                            
                                            <Dialog v-if="isAdmin" v-model:open="isResetOdooSyncDialogOpen">
                                                <DialogTrigger as-child>
                                                    <Button variant="ghost" size="sm" class="h-7 text-xs text-muted-foreground hover:text-red-600">Reset</Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Reset Odoo Sync Status</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to reset the Odoo sync status?
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <DialogFooter>
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button variant="destructive" @click="resetOdooSync">Reset Sync Status</Button>
                                                    </DialogFooter>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                        
                                        <div v-if="getLatestLog('entered_to_odoo')" class="text-[10px] text-muted-foreground pl-1">
                                            Last synced by {{ getLatestLog('entered_to_odoo').approver?.name }} on {{ formatDate(getLatestLog('entered_to_odoo').created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="rounded-md border bg-white dark:bg-zinc-950 overflow-x-auto">
                <Table>
                    <TableHeader class="bg-zinc-50 dark:bg-zinc-900/50">
                        <TableRow>
                            <TableHead class="font-bold text-xs uppercase text-zinc-500">Product</TableHead>
                            <TableHead class="font-bold text-xs uppercase text-zinc-500">Description</TableHead>
                            <TableHead class="text-center font-bold text-xs uppercase text-zinc-500">Quantity</TableHead>
                            <TableHead class="text-center font-bold text-xs uppercase text-zinc-500">Delivered</TableHead>
                            <TableHead class="text-center font-bold text-xs uppercase text-zinc-500">Invoiced</TableHead>
                            <TableHead class="text-right font-bold text-xs uppercase text-zinc-500">Unit Price</TableHead>
                            <TableHead class="text-right font-bold text-xs uppercase text-zinc-500">Taxes</TableHead>
                            <TableHead class="text-right font-bold text-xs uppercase text-zinc-500">Disc.%</TableHead>
                            <TableHead class="text-right font-bold text-xs uppercase text-zinc-500">Tax Excl.</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="line in salesOrder.lines"
                            :key="line.id"
                        >
                            <TableCell class="font-medium text-xs break-words max-w-[200px] text-zinc-600 dark:text-zinc-300">
                                {{ line.product_name || '-' }}
                            </TableCell>
                            <TableCell class="text-xs break-words max-w-[250px] text-zinc-500">
                                {{ line.name || '-' }}
                            </TableCell>
                            <TableCell class="text-center text-xs text-zinc-600 dark:text-zinc-300">
                                {{ Number(line.product_uom_qty || 0).toString() }}
                            </TableCell>
                            <TableCell class="text-center text-xs text-zinc-600 dark:text-zinc-300">
                                {{ Number(line.qty_delivered || 0).toString() }}
                            </TableCell>
                            <TableCell class="text-center text-xs text-zinc-600 dark:text-zinc-300">
                                {{ Number(line.qty_invoiced || 0).toString() }}
                            </TableCell>
                            <TableCell class="text-right text-xs text-zinc-600 dark:text-zinc-300">
                                {{ line.price_unit ? formatCurrency(line.price_unit) : '' }}
                            </TableCell>
                            <TableCell class="text-right text-xs text-zinc-400">
                                {{ line.tax_names || '' }}
                            </TableCell>
                            <TableCell class="text-right text-xs text-zinc-600 dark:text-zinc-300">
                                {{ Number(line.discount || 0).toString() }}
                            </TableCell>
                            <TableCell class="text-right text-xs text-zinc-600 dark:text-zinc-300">
                                {{ formatCurrency(line.price_subtotal) }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="salesOrder.lines.length === 0">
                            <TableCell colspan="9" class="h-24 text-center">
                                No lines found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-end pt-4 pr-4">
                <div class="w-64 space-y-1.5">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-zinc-600 dark:text-zinc-300">Untaxed Amount:</span>
                        <span class="font-bold">{{ formatCurrency(salesOrder.amount_untaxed || 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-zinc-500">
                        <span>GST 10%:</span>
                        <span>{{ formatCurrency(salesOrder.amount_tax || 0) }}</span>
                    </div>
                    <div class="flex justify-between text-base pt-1">
                        <span class="font-normal text-zinc-600 dark:text-zinc-400">Total:</span>
                        <span class="font-bold">{{ formatCurrency(salesOrder.amount_total || 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
