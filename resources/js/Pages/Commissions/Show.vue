<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { ref } from 'vue';
import {
    ArrowLeft,
    CheckCircle2,
    XCircle,
    Calculator,
    Edit,
    Info,
    RotateCcw,
} from 'lucide-vue-next';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

const props = defineProps<{
    commission: any;
}>();

const adjustmentDialogOpen = ref(false);
const approveDialogOpen = ref(false);
const rejectDialogOpen = ref(false);

const adjustmentForm = useForm({
    adjustment_amount: 0,
    reason: '',
});

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    reason: '',
});

const openAdjustmentDialog = () => {
    adjustmentForm.adjustment_amount = props.commission.manual_adjustment || 0;
    adjustmentForm.reason = '';
};

const submitAdjustment = () => {
    adjustmentForm.post(route('commissions.adjust', props.commission.id), {
        onSuccess: () => {
            adjustmentDialogOpen.value = false;
            adjustmentForm.reset();
        },
    });
};

const approveCommission = () => {
    approveForm.post(route('commissions.approve', props.commission.id), {
        onSuccess: () => {
            approveDialogOpen.value = false;
            approveForm.reset();
        },
    });
};

const rejectCommission = () => {
    rejectForm.post(route('commissions.reject', props.commission.id), {
        onSuccess: () => {
            rejectDialogOpen.value = false;
            rejectForm.reset();
        },
    });
};

const recalculateCommission = () => {
    router.post(route('commissions.recalculate', props.commission.id));
};

const resetConfirmation = () => {
    if (confirm('Are you sure you want to reset manager confirmation? This will move the commission back to pending.')) {
        router.post(route('commissions.reset-confirm', props.commission.id));
    }
};

const resetOdooSync = () => {
    if (confirm('Are you sure you want to reset Odoo sync status?')) {
        router.post(route('commissions.reset-odoo', props.commission.id));
    }
};

const formatCurrency = (amount: number | null) => {
    if (amount === null || amount === undefined) return '$0.00';
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-AU');
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
</script>

<template>
    <Head :title="`Commission #${commission.id}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('commissions.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Commission #{{ commission.id }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Sales Order:
                            <Link
                                :href="route('sales-orders.show', commission.sales_order.id)"
                                class="text-primary hover:underline"
                            >
                                {{ commission.sales_order.name }}
                            </Link>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge :variant="getStatusBadgeVariant(commission.status)" class="text-sm">
                        {{ commission.status }}
                    </Badge>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2" v-if="commission.status === 'pending'">
                <!-- Approve Button -->
                <Dialog v-model:open="approveDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="default" size="sm">
                            <CheckCircle2 class="mr-2 h-4 w-4" />
                            Approve
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Approve Commission</DialogTitle>
                            <DialogDescription>
                                Approve this commission for payment. You can add optional notes.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="notes">Notes (Optional)</Label>
                                <Textarea
                                    id="notes"
                                    v-model="approveForm.notes"
                                    placeholder="Add any approval notes..."
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button variant="outline" @click="approveDialogOpen = false">Cancel</Button>
                            <Button @click="approveCommission" :disabled="approveForm.processing">
                                Approve Commission
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Reject Button -->
                <Dialog v-model:open="rejectDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="destructive" size="sm">
                            <XCircle class="mr-2 h-4 w-4" />
                            Reject
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Reject Commission</DialogTitle>
                            <DialogDescription>
                                Provide a reason for rejecting this commission.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="reason">Reason *</Label>
                                <Textarea
                                    id="reason"
                                    v-model="rejectForm.reason"
                                    placeholder="Explain why this commission is being rejected..."
                                    required
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button variant="outline" @click="rejectDialogOpen = false">Cancel</Button>
                            <Button
                                variant="destructive"
                                @click="rejectCommission"
                                :disabled="rejectForm.processing || rejectForm.reason.length < 10"
                            >
                                Reject Commission
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Adjust Button -->
                <Dialog v-model:open="adjustmentDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm" @click="openAdjustmentDialog">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Adjustment
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Total Manual Adjustment</DialogTitle>
                            <DialogDescription>
                                Set the final total manual adjustment for this commission. The system will record any changes in the history.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="amount">Total Adjustment Amount *</Label>
                                <Input
                                    id="amount"
                                    v-model.number="adjustmentForm.adjustment_amount"
                                    type="number"
                                    step="0.01"
                                    placeholder="-1200.00"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">
                                    Current Total: {{ formatCurrency(commission.manual_adjustment) }}
                                </p>
                            </div>
                            <div class="grid gap-2">
                                <Label for="adj-reason">Reason *</Label>
                                <Input
                                    id="adj-reason"
                                    v-model="adjustmentForm.reason"
                                    placeholder="e.g. Setting fixed commission to -1200"
                                    required
                                />
                                <div v-if="commission.last_adjustment_note" class="mt-2 p-2 bg-muted rounded text-[10px]">
                                    <span class="font-semibold">Last Note:</span> {{ commission.last_adjustment_note }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button variant="outline" @click="adjustmentDialogOpen = false">Cancel</Button>
                            <Button
                                @click="submitAdjustment"
                                :disabled="adjustmentForm.processing || !adjustmentForm.reason"
                            >
                                Update Total Adjustment
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Recalculate Button -->
                <Button variant="outline" size="sm" @click="recalculateCommission">
                    <Calculator class="mr-2 h-4 w-4" />
                    Recalculate
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content - Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Commission Breakdown Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Commission Breakdown</CardTitle>
                            <CardDescription>
                                Detailed calculation for this commission
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Formula Display -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Base Commission</span>
                                    <span class="text-lg font-bold">
                                        {{ formatCurrency(commission.base_commission) }}
                                    </span>
                                </div>
                                
                                <Separator />
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Extra Commission (Profit-based)</span>
                                    <span class="text-lg font-bold" :class="commission.extra_commission < 0 ? 'text-destructive' : 'text-green-600'">
                                        {{ formatCurrency(commission.extra_commission) }}
                                    </span>
                                </div>

                                <div class="ml-4 space-y-2 text-sm text-muted-foreground">
                                    <div class="flex items-center justify-between">
                                        <span>Selling Price</span>
                                        <span>{{ formatCurrency(commission.selling_price) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>- Additional Cost</span>
                                        <span>{{ formatCurrency(commission.additional_cost) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>- Landing Price</span>
                                        <span>{{ formatCurrency(commission.landing_price) }}</span>
                                    </div>
                                    <Separator class="my-2" />
                                    <div class="flex items-center justify-between font-semibold" :class="commission.profit < 0 ? 'text-destructive' : 'text-green-600'">
                                        <span>= Profit</span>
                                        <span>{{ formatCurrency(commission.profit) }}</span>
                                    </div>
                                </div>

                                <Separator v-if="commission.manual_adjustment !== 0" />
                                
                                <div v-if="commission.manual_adjustment !== 0" class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">Manual Adjustments</span>
                                        <TooltipProvider v-if="commission.last_adjustment_note">
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Info class="h-4 w-4 text-muted-foreground cursor-help" />
                                                </TooltipTrigger>
                                                <TooltipContent class="max-w-xs">
                                                    <p class="font-semibold mb-1">Latest Note:</p>
                                                    <p class="text-xs">{{ commission.last_adjustment_note }}</p>
                                                    <p class="text-[10px] text-muted-foreground mt-1">by {{ commission.last_adjustment_by || 'Unknown' }}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                    <span class="text-lg font-bold" :class="commission.manual_adjustment < 0 ? 'text-destructive' : 'text-green-600'">
                                        {{ formatCurrency(commission.manual_adjustment) }}
                                    </span>
                                </div>

                                <Separator />

                                <div class="flex items-center justify-between text-lg">
                                    <span class="font-bold">Final Commission</span>
                                    <span class="text-2xl font-bold text-primary">
                                        {{ formatCurrency(commission.final_commission) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Adjustments History -->
                    <Card v-if="commission.adjustments && commission.adjustments.length > 0">
                        <CardHeader>
                            <CardTitle>Adjustment History</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Reason</TableHead>
                                        <TableHead>Adjusted By</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="adjustment in commission.adjustments" :key="adjustment.id">
                                        <TableCell>{{ formatDate(adjustment.created_at) }}</TableCell>
                                        <TableCell :class="adjustment.adjustment_amount < 0 ? 'text-destructive' : 'text-green-600'">
                                            {{ formatCurrency(adjustment.adjustment_amount) }}
                                        </TableCell>
                                        <TableCell>{{ adjustment.reason }}</TableCell>
                                        <TableCell>{{ adjustment.adjuster?.name || 'N/A' }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <!-- Approval History -->
                    <Card v-if="commission.approvals && commission.approvals.length > 0">
                        <CardHeader>
                            <CardTitle>Approval History</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Action</TableHead>
                                        <TableHead>Notes</TableHead>
                                        <TableHead>By</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="approval in commission.approvals" :key="approval.id">
                                        <TableCell>{{ formatDate(approval.created_at) }}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{{ approval.action }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ approval.notes || '-' }}</TableCell>
                                        <TableCell>{{ approval.approver?.name || 'N/A' }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar - Right Column -->
                <div class="space-y-6">
                    <!-- Sales Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Sales Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Salesperson</div>
                                <div class="text-sm">{{ commission.user?.name || 'N/A' }}</div>
                            </div>
                            <Separator />
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Sales Manager</div>
                                <div class="text-sm">{{ commission.sales_manager?.name || 'Not assigned' }}</div>
                            </div>
                            <Separator />
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Sales Source</div>
                                <Badge :variant="commission.sales_source === 'self_gen' ? 'default' : 'secondary'" class="mt-1">
                                    {{ commission.sales_source === 'self_gen' ? 'Self Gen' : 'Company Lead' }}
                                </Badge>
                            </div>
                            <Separator />
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Payment Type</div>
                                <div class="text-sm">{{ commission.payment_type }}</div>
                            </div>
                            <Separator />
                            <div v-if="commission.is_special_product">
                                <Badge variant="default">Special Product ($200 Fixed)</Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground">Current Status</div>
                                <Badge :variant="getStatusBadgeVariant(commission.status)" class="mt-1">
                                    {{ commission.status }}
                                </Badge>
                            </div>
                            <Separator v-if="commission.approved_at" />
                            <div v-if="commission.approved_at">
                                <div class="text-sm font-medium text-muted-foreground">Approved</div>
                                <div class="text-sm">{{ formatDate(commission.approved_at) }}</div>
                                <div class="text-xs text-muted-foreground">by {{ commission.approver?.name }}</div>
                            </div>
                            <Separator v-if="commission.rejected_at" />
                            <div v-if="commission.rejected_at">
                                <div class="text-sm font-medium text-muted-foreground">Rejected</div>
                                <div class="text-sm">{{ formatDate(commission.rejected_at) }}</div>
                                <div class="text-xs text-muted-foreground">by {{ commission.rejecter?.name }}</div>
                                <div class="text-xs mt-2">Reason: {{ commission.rejection_reason }}</div>
                            </div>
                            <Separator v-if="commission.paid_at" />
                            <div v-if="commission.paid_at">
                                <div class="text-sm font-medium text-muted-foreground">Paid</div>
                                <div class="text-sm">{{ formatDate(commission.paid_at) }}</div>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-medium text-muted-foreground">Manager Confirmed:</div>
                                <div class="flex items-center gap-2">
                                    <Badge :variant="commission.confirmed_by_manager ? 'default' : 'secondary'">
                                        {{ commission.confirmed_by_manager ? 'Yes' : 'No' }}
                                    </Badge>
                                    <Button 
                                        v-if="commission.confirmed_by_manager" 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-6 w-6"
                                        title="Reset Confirmation"
                                        @click="resetConfirmation"
                                    >
                                        <RotateCcw class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-medium text-muted-foreground">Entered to Odoo:</div>
                                <div class="flex items-center gap-2">
                                    <Badge :variant="commission.entered_to_odoo ? 'default' : 'secondary'">
                                        {{ commission.entered_to_odoo ? 'Yes' : 'No' }}
                                    </Badge>
                                    <Button 
                                        v-if="commission.entered_to_odoo" 
                                        variant="ghost" 
                                        size="icon" 
                                        class="h-6 w-6"
                                        title="Reset Odoo Sync"
                                        @click="resetOdooSync"
                                    >
                                        <RotateCcw class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
