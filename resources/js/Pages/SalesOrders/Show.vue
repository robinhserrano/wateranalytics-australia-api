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
import { ref, watch, computed, onMounted } from 'vue';
import { CheckCircle2, XCircle, AlertCircle, Send, User, MapPin, Info, Calendar, Loader2, RefreshCw, File, ExternalLink } from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
    salesOrder: any;
    calculationError?: string | null;
    canViewInstaller?: boolean;
    canConfirmCommission?: boolean;
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

// ─── Toast Notification ────────────────────────────────────────────────────────
interface Toast {
    id: number;
    type: 'success' | 'error' | 'warning';
    message: string;
}
const toasts = ref<Toast[]>([]);
let toastCounter = 0;

const showToast = (message: string, type: 'success' | 'error' | 'warning' = 'success') => {
    const id = ++toastCounter;
    toasts.value.push({ id, type, message });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 5000);
};

const dismissToast = (id: number) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

const page = usePage<any>();
const isAdmin = computed(() => {
    return (page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Admin');
});

const isSalesManager = computed(() => {
    return (page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Sales Manager');
});

const showInstallationTab = computed(() => {
    return isAdmin.value || isSalesManager.value;
});

// Watch for Inertia flash messages and display them as toasts
watch(() => page.props.flash as any, (flash) => {
    if (flash?.success) showToast(flash.success, 'success');
    if (flash?.error)   showToast(flash.error, 'error');
}, { immediate: true });

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
    // Frontend guard: Sales Managers need full delivery
    if (!isAdmin.value && isSalesManager.value) {
        if (props.salesOrder.delivery_status !== 'full') {
            isConfirmDialogOpen.value = false;
            showToast('The sales order must be fully delivered before you can confirm the commission.', 'warning');
            return;
        }
    }

    router.post(route('commissions.confirm', props.salesOrder.commission_calculation.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isConfirmDialogOpen.value = false;
        },
        onError: () => {
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

// ─── Odoo Messages state ───────────────────────────────────────────────────────

interface Author {
    id: number;
    type: string;
}

interface TrackingValue {
    id: number;
    changedField: string;
    fieldName: string;
    fieldType: string;
    newValue: { value: any };
    oldValue: { value: any };
}

interface Attachment {
    id: number;
    name: string;
    filename: string;
    mimetype: string;
    size: number;
    checksum: string;
}

interface Message {
    id: number;
    author: Author;
    body: string;
    date: string;
    is_note: boolean;
    is_discussion: boolean;
    message_type: string;
    attachment_ids: number[];
    trackingValues: TrackingValue[];
    record_name: string;
}

interface Partner {
    id: number;
    name: string;
    userId: number;
    isInternalUser: boolean;
}

const messages = ref<Message[]>([]);
const partners = ref<Record<number, Partner>>({});
const attachments = ref<Record<number, Attachment>>({});
const loadingMessages = ref(false);
const messagesError = ref<string | null>(null);

// Group messages by day (Newest first)
const groupedMessages = computed(() => {
    const groups: Record<string, Message[]> = {};
    [...messages.value]
        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
        .forEach((msg) => {
            const day = new Date(msg.date).toLocaleDateString('en-AU', {
                year: 'numeric', month: 'long', day: 'numeric'
            });
            if (!groups[day]) groups[day] = [];
            groups[day].push(msg);
        });
    return groups;
});

const fetchMessages = async () => {
    if (!props.salesOrder.odoo_task_id) {
        messagesError.value = 'No Odoo task linked to this sales order.';
        return;
    }

    loadingMessages.value = true;
    messagesError.value = null;

    try {
        const response = await fetch(route('installation-tasks.messages', props.salesOrder.id));
        const data = await response.json();

        if (data.error) {
            messagesError.value = data.error;
            return;
        }

        const result = data.result;
        const msgList: Message[] = (result?.messages ?? []).map((id: number) => {
            const mails = result?.data?.['mail.message'] ?? [];
            return mails.find((m: Message) => m.id === id);
        }).filter(Boolean);

        messages.value = msgList;

        // Build partner lookup
        const partnerList: Partner[] = result?.data?.['res.partner'] ?? [];
        partnerList.forEach((p) => { partners.value[p.id] = p; });

        // Build attachment lookup
        const attachList: Attachment[] = result?.data?.['ir.attachment'] ?? [];
        attachList.forEach((a) => { attachments.value[a.id] = a; });

    } catch (e) {
        messagesError.value = 'Failed to load messages. Please try again.';
    } finally {
        loadingMessages.value = false;
    }
};

onMounted(() => {
    if (showInstallationTab.value && props.salesOrder.odoo_task_id) {
        fetchMessages();
    }
});

// ─── Helpers ───────────────────────────────────────────────────────────────────

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-AU', { hour: '2-digit', minute: '2-digit' });
};

const getAuthorName = (author: Author) => {
    const p = partners.value[author.id];
    return p?.name ?? 'Unknown';
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const getAuthorColor = (authorId: number) => {
    const colors = [
        'bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-rose-500',
        'bg-amber-500', 'bg-teal-500', 'bg-indigo-500', 'bg-pink-500',
    ];
    return colors[authorId % colors.length];
};

const isImageAttachment = (att: Attachment) => att.mimetype?.startsWith('image/');

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
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

            <!-- ─── Toast Notifications ──────────────────────────────────────── -->
            <Teleport to="body">
                <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 w-full max-w-sm pointer-events-none">
                    <TransitionGroup name="toast">
                        <div
                            v-for="toast in toasts"
                            :key="toast.id"
                            class="pointer-events-auto flex items-start gap-3 rounded-lg border px-4 py-3 shadow-lg"
                            :class="{
                                'bg-green-50 border-green-200 text-green-900 dark:bg-green-950/80 dark:border-green-800 dark:text-green-100': toast.type === 'success',
                                'bg-red-50 border-red-200 text-red-900 dark:bg-red-950/80 dark:border-red-800 dark:text-red-100': toast.type === 'error',
                                'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-950/80 dark:border-amber-800 dark:text-amber-100': toast.type === 'warning',
                            }"
                        >
                            <span class="mt-0.5 shrink-0">
                                <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="size-5 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <svg v-else-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="size-5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-5 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            </span>
                            <p class="flex-1 text-sm font-medium leading-snug">{{ toast.message }}</p>
                            <button @click="dismissToast(toast.id)" class="ml-2 shrink-0 opacity-60 hover:opacity-100 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </TransitionGroup>
                </div>
            </Teleport>

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
                            <div class="flex items-center justify-between text-sm" v-if="salesOrder.has_installation">
                                <span class="text-muted-foreground">Est. Install Date</span>
                                <span :class="salesOrder.installation_date ? 'font-bold text-blue-600' : 'italic text-muted-foreground'">
                                    {{ salesOrder.installation_date ? formatDate(salesOrder.installation_date) : 'Not Set' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm" v-if="salesOrder.odoo_task_id">
                                <span class="text-muted-foreground">Odoo Task</span>
                                <span class="font-bold text-xs text-blue-600 hover:underline">
                                    <a :href="`https://wateranalytics.odoo.com/web#id=${salesOrder.odoo_task_id}&model=project.task&view_type=form`" target="_blank">
                                        #{{ salesOrder.odoo_task_id }}
                                    </a>
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

            <Tabs default-value="details" class="w-full">
                <TabsList v-if="showInstallationTab" class="mb-4">
                    <TabsTrigger value="details">Order Details & Commission</TabsTrigger>
                    <TabsTrigger value="installation">Installation Task</TabsTrigger>
                </TabsList>

                <TabsContent value="details" class="space-y-4">
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
                                            <template v-if="!salesOrder.commission_calculation.confirmed_by_manager && canConfirmCommission">
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
                </TabsContent>

                <TabsContent value="installation" v-if="showInstallationTab">
                    <Card class="w-full">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center justify-between text-base">
                                <span>Log Notes</span>
                                <Button variant="outline" size="sm" class="h-8 gap-1.5" @click="fetchMessages" :disabled="loadingMessages">
                                    <Loader2 v-if="loadingMessages" class="size-3.5 animate-spin" />
                                    <RefreshCw v-else class="size-3.5" />
                                    Refresh
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>

                            <!-- Loading -->
                            <div v-if="loadingMessages" class="flex items-center justify-center py-12 gap-2 text-muted-foreground">
                                <Loader2 class="size-5 animate-spin" />
                                <span class="text-sm">Loading messages from Odoo...</span>
                            </div>

                            <!-- Error -->
                            <div v-else-if="messagesError" class="flex items-start gap-3 p-4 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300">
                                <AlertCircle class="size-4 mt-0.5 shrink-0" />
                                <span class="text-sm">{{ messagesError }}</span>
                            </div>

                            <!-- Empty state / Not linked -->
                            <div v-else-if="!salesOrder.odoo_task_id" class="text-center py-12 text-muted-foreground text-sm">
                                No Odoo task linked to this sales order.
                            </div>
                            
                            <div v-else-if="messages.length === 0" class="text-center py-12 text-muted-foreground text-sm">
                                No log notes found for this task.
                            </div>

                            <!-- Messages grouped by date -->
                            <div v-else class="space-y-8">
                                <div v-for="(dayMessages, day) in groupedMessages" :key="day">

                                    <!-- Day divider -->
                                    <div class="flex items-center gap-3 my-4">
                                        <div class="flex-1 border-t border-border/60"></div>
                                        <span class="text-xs font-semibold text-muted-foreground px-2">{{ day }}</span>
                                        <div class="flex-1 border-t border-border/60"></div>
                                    </div>

                                    <div class="space-y-5">
                                        <div v-for="msg in dayMessages" :key="msg.id" class="flex gap-3">

                                            <!-- Avatar -->
                                            <div
                                                class="size-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5"
                                                :class="getAuthorColor(msg.author.id)"
                                            >
                                                {{ getInitials(getAuthorName(msg.author)) }}
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <!-- Header row -->
                                                <div class="flex items-baseline gap-2 mb-1.5">
                                                    <span class="font-semibold text-sm">{{ getAuthorName(msg.author) }}</span>
                                                    <span class="text-xs text-muted-foreground">{{ formatTime(msg.date) }}</span>
                                                    <span
                                                        v-if="msg.is_note"
                                                        class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-medium"
                                                    >Note</span>
                                                    <span
                                                        v-else-if="msg.message_type === 'notification'"
                                                        class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 font-medium"
                                                    >Activity</span>
                                                </div>

                                                <!-- Body text -->
                                                <div
                                                    v-if="msg.body"
                                                    class="text-sm leading-relaxed text-foreground prose dark:prose-invert max-w-none"
                                                    v-html="msg.body"
                                                ></div>

                                                <!-- Tracking values (e.g. Low → High) -->
                                                <div v-if="msg.trackingValues?.length" class="mt-2 space-y-1">
                                                    <div
                                                        v-for="tv in msg.trackingValues"
                                                        :key="tv.id"
                                                        class="flex items-center gap-2 text-xs text-muted-foreground"
                                                    >
                                                        <span class="font-medium text-foreground">{{ tv.changedField }}</span>
                                                        <span>{{ tv.oldValue?.value ?? '—' }}</span>
                                                        <span class="text-muted-foreground/50">→</span>
                                                        <span class="font-semibold text-foreground">{{ tv.newValue?.value ?? '—' }}</span>
                                                    </div>
                                                </div>

                                                <!-- Attachments -->
                                                <div v-if="msg.attachment_ids?.length" class="mt-3 flex flex-wrap gap-2">
                                                    <template v-for="attId in msg.attachment_ids" :key="attId">
                                                        <div v-if="attachments[attId]">
                                                            <!-- Image preview -->
                                                            <Dialog v-if="isImageAttachment(attachments[attId])">
                                                                <DialogTrigger as-child>
                                                                    <div class="relative group rounded-lg overflow-hidden border border-border cursor-pointer hover:ring-2 hover:ring-primary/50 transition-all">
                                                                        <img
                                                                            :src="route('installation-tasks.attachment', attId)"
                                                                            :alt="attachments[attId].name"
                                                                            class="max-w-[220px] max-h-[160px] object-cover block"
                                                                            @error="(e: Event) => ((e.target as HTMLElement).style.display = 'none')"
                                                                        />
                                                                        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
                                                                        <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[10px] px-2 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity truncate backdrop-blur-sm">
                                                                            {{ attachments[attId].filename }}
                                                                        </div>
                                                                    </div>
                                                                </DialogTrigger>
                                                                <DialogContent class="sm:max-w-3xl border-border bg-background p-0 overflow-hidden">
                                                                    <DialogHeader class="p-4 border-b border-border bg-muted/30">
                                                                        <DialogTitle class="text-base truncate pr-6">{{ attachments[attId].filename }}</DialogTitle>
                                                                    </DialogHeader>
                                                                    <div class="p-6 flex flex-col items-center justify-center gap-6 bg-muted/10">
                                                                        <div class="rounded-md overflow-hidden border border-border shadow-sm max-w-full bg-black/5 dark:bg-white/5 flex items-center justify-center p-2">
                                                                            <img 
                                                                                :src="route('installation-tasks.attachment', attId)" 
                                                                                class="max-h-[65vh] object-contain rounded" 
                                                                                :alt="attachments[attId].filename"
                                                                            />
                                                                        </div>
                                                                        <Button as-child variant="outline" class="gap-2">
                                                                            <a :href="`https://wateranalytics.odoo.com/web/image/${attId}`" target="_blank">
                                                                                <ExternalLink class="size-4" />
                                                                                Open Original Image
                                                                            </a>
                                                                        </Button>
                                                                    </div>
                                                                </DialogContent>
                                                            </Dialog>

                                                            <!-- File badge -->
                                                            <div
                                                                v-else
                                                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-border bg-muted/30 text-xs"
                                                            >
                                                                <File class="size-3.5 text-muted-foreground" />
                                                                <span class="max-w-[160px] truncate">{{ attachments[attId].filename }}</span>
                                                                <span class="text-muted-foreground/60">{{ formatFileSize(attachments[attId].size) }}</span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>

</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
