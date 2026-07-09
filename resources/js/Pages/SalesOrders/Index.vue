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
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
    SheetFooter,
} from '@/components/ui/sheet';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar as CalendarPicker } from '@/components/ui/calendar';
import { DateFormatter, type DateValue, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search, Filter, X, RotateCcw, ChevronDown, Calendar, ChevronLeft, ChevronRight, Loader2 } from 'lucide-vue-next';

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
        commission_status?: string[];
        invoice_status?: string[];
        delivery_status?: string[];
        manager_confirmation_status?: string[];
        user_ids?: string[];
        date_from?: string;
        date_to?: string;
    };
    users: Array<{ id: number; name: string; roles?: Array<{ id: number; name: string }> }>;
    viewScope?: string;
    canViewInstaller?: boolean;
    canConfirmCommission?: boolean;
    canMarkOdoo?: boolean;
}>();

// Persist filters per-browser so they survive closing the tab / opening fresh.
// Only used when the URL itself has no filters (a real filtered link always wins).
const FILTERS_STORAGE_KEY = 'sales-orders-filters';
const hasUrlFilters = Object.values(props.filters).some(v => v && (!Array.isArray(v) || v.length));
const cachedFilters = !hasUrlFilters ? JSON.parse(localStorage.getItem(FILTERS_STORAGE_KEY) || '{}') : {};
const initialFilters = { ...cachedFilters, ...props.filters };

const search = ref(initialFilters.search || '');

const ensureArray = (val: any): string[] => {
    if (!val) return [];
    return Array.isArray(val) ? val : [val];
};

const commissionStatus = ref<string[]>(ensureArray(initialFilters.commission_status));
const invoiceStatus = ref<string[]>(ensureArray(initialFilters.invoice_status));
const deliveryStatus = ref<string[]>(ensureArray(initialFilters.delivery_status));
const managerConfirmationStatus = ref<string[]>(ensureArray(initialFilters.manager_confirmation_status));
const selectedUserIds = ref<string[]>(ensureArray(initialFilters.user_ids));
const dateFrom = ref<string>(initialFilters.date_from || '');
const dateTo = ref<string>(initialFilters.date_to || '');

watch([search, commissionStatus, invoiceStatus, deliveryStatus, managerConfirmationStatus, selectedUserIds, dateFrom, dateTo], () => {
    localStorage.setItem(FILTERS_STORAGE_KEY, JSON.stringify({
        search: search.value,
        commission_status: commissionStatus.value,
        invoice_status: invoiceStatus.value,
        delivery_status: deliveryStatus.value,
        manager_confirmation_status: managerConfirmationStatus.value,
        user_ids: selectedUserIds.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }));
}, { deep: true });

const managerConfirmationOptions = ['Confirmed', 'Not Confirmed'];

const userSearchQuery = ref('');
const isUserSearchOpen = ref(false);

const filteredUsers = computed(() => {
    const pendingOption = { id: 'pending', name: 'Pending Mapping' };
    let list = props.users.map(user => ({ id: String(user.id), name: user.name }));
    
    // Always include 'Pending Mapping' at the top
    list = [pendingOption, ...list];

    if (!userSearchQuery.value) return list;
    const query = userSearchQuery.value.toLowerCase();
    return list.filter(item => item.name.toLowerCase().includes(query));
});

const isFilterSheetOpen = ref(false);

const applyFilters = () => {
    router.get(
        route('sales-orders.index'),
        {
            search: search.value,
            commission_status: commissionStatus.value,
            invoice_status: invoiceStatus.value,
            delivery_status: deliveryStatus.value,
            manager_confirmation_status: managerConfirmationStatus.value,
            user_ids: selectedUserIds.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true }
    );
    isFilterSheetOpen.value = false;
};

// If we loaded cached filters that weren't already in the URL, apply them now
// so the results list matches what the filter sheet shows.
if (!hasUrlFilters && Object.keys(cachedFilters).length) {
    applyFilters();
}

const resetFilters = () => {
    commissionStatus.value = [];
    invoiceStatus.value = [];
    deliveryStatus.value = [];
    managerConfirmationStatus.value = [];
    selectedUserIds.value = [];
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const setPayableCommissionsFilter = () => {
    commissionStatus.value = ['Not Paid'];
    invoiceStatus.value = ['Paid'];
    deliveryStatus.value = ['Fully Delivered'];
    managerConfirmationStatus.value = [];
    
    const nonInternalUsers = props.users.filter(u => {
        return !u.roles?.some(r => r.name === 'Sales - Internal');
    }).map(u => String(u.id));
    
    nonInternalUsers.push('pending');
    
    selectedUserIds.value = nonInternalUsers;
};

const setPayableCommissionsAccountingFilter = () => {
    commissionStatus.value = ['Not Paid'];
    invoiceStatus.value = ['Paid'];
    deliveryStatus.value = ['Fully Delivered'];
    managerConfirmationStatus.value = ['Confirmed'];
    
    const nonInternalUsers = props.users.filter(u => {
        return !u.roles?.some(r => r.name === 'Sales - Internal');
    }).map(u => String(u.id));
    
    nonInternalUsers.push('pending');
    
    selectedUserIds.value = nonInternalUsers;
};

const isPayableCommissionsActive = computed(() => {
    if (commissionStatus.value.length !== 1 || commissionStatus.value[0] !== 'Not Paid' ||
        invoiceStatus.value.length !== 1 || invoiceStatus.value[0] !== 'Paid' ||
        deliveryStatus.value.length !== 1 || deliveryStatus.value[0] !== 'Fully Delivered' ||
        managerConfirmationStatus.value.length !== 0) {
        return false;
    }
    
    const nonInternalUsers = props.users.filter(u => {
        return !u.roles?.some(r => r.name === 'Sales - Internal');
    }).map(u => String(u.id));
    nonInternalUsers.push('pending');
    
    if (selectedUserIds.value.length !== nonInternalUsers.length) return false;
    return nonInternalUsers.every(id => selectedUserIds.value.includes(id));
});

const isPayableCommissionsAccountingActive = computed(() => {
    if (commissionStatus.value.length !== 1 || commissionStatus.value[0] !== 'Not Paid' ||
        invoiceStatus.value.length !== 1 || invoiceStatus.value[0] !== 'Paid' ||
        deliveryStatus.value.length !== 1 || deliveryStatus.value[0] !== 'Fully Delivered' ||
        managerConfirmationStatus.value.length !== 1 || managerConfirmationStatus.value[0] !== 'Confirmed') {
        return false;
    }
    
    const nonInternalUsers = props.users.filter(u => {
        return !u.roles?.some(r => r.name === 'Sales - Internal');
    }).map(u => String(u.id));
    nonInternalUsers.push('pending');
    
    if (selectedUserIds.value.length !== nonInternalUsers.length) return false;
    return nonInternalUsers.every(id => selectedUserIds.value.includes(id));
});

const commissionOptions = ['Paid', 'Not Paid'];
const invoiceOptions = ['Paid', 'Partial', 'Not Paid', 'Not Set'];
const deliveryOptions = ['Fully Delivered', 'Partially Delivered', 'Not Delivered'];

const toggleFilter = (group: string[], value: string) => {
    const index = group.indexOf(value);
    if (index > -1) {
        group.splice(index, 1);
    } else {
        group.push(value);
    }
};

const handleSearch = useDebounceFn((value: string) => {
    router.get(
        route('sales-orders.index'),
        { 
            search: value,
            commission_status: commissionStatus.value,
            invoice_status: invoiceStatus.value,
            delivery_status: deliveryStatus.value,
            user_ids: selectedUserIds.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => {
    handleSearch(value);
});

const df = new DateFormatter('en-AU', {
    dateStyle: 'medium',
});

const parseCalendarDate = (val: string) => {
    if (!val) return undefined;
    try {
        return parseDate(val);
    } catch (e) {
        return undefined;
    }
};

const displayDate = (dateStr: string) => {
    if (!dateStr) return 'Select date';
    try {
        const d = parseDate(dateStr);
        return df.format(d.toDate(getLocalTimeZone()));
    } catch (e) {
        return dateStr;
    }
};

const addUser = (id: number | string) => {
    const idStr = String(id);
    if (!selectedUserIds.value.includes(idStr)) {
        selectedUserIds.value.push(idStr);
    }
    userSearchQuery.value = '';
    isUserSearchOpen.value = false;
};

const removeUser = (id: string) => {
    selectedUserIds.value = selectedUserIds.value.filter(uid => uid !== id);
};

const displayedUserIds = computed(() => selectedUserIds.value.slice(0, 3));
const othersCount = computed(() => Math.max(0, selectedUserIds.value.length - 3));

const getActiveFilterCount = () => {
    let count = 0;
    count += commissionStatus.value.length;
    count += invoiceStatus.value.length;
    count += deliveryStatus.value.length;
    count += selectedUserIds.value.length;
    if (dateFrom.value) count++;
    if (dateTo.value) count++;
    return count;
};

const getSelectedUserNames = () => {
    return props.users
        .filter(u => selectedUserIds.value.includes(String(u.id)))
        .map(u => u.name);
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

const formatSource = (source: string | null) => {
    if (!source) return '-';
    return source === 'self_gen' ? 'Self Gen' : 'Company Lead';
};

const getBadgeStyles = (source: string | null) => {
    if (source === 'self_gen') {
        return {
            color: '#5850e6',
            backgroundColor: '#eef2ff',
            borderColor: '#5850e6'
        };
    }
    // Default for Company Lead and others
    return {
        color: '#475569',
        backgroundColor: '#f1f5f9',
        borderColor: '#cbd5e1'
    };
};

const formatBoolean = (val: any) => {
    return val ? 'Yes' : 'No';
};

const formatDeliveryStatus = (status: string | null) => {
    if (!status || status === '0') return 'Not Delivered';
    const s = status.toLowerCase();
    if (s === 'full' || s === 'done') return 'Fully Delivered';
    if (s === 'partial' || s === 'started' || s === 'pending') return 'Partially Delivered';
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getDeliveryBadgeStyles = (status: string | null) => {
    if (!status || status === '0') {
        // Not Delivered - default slate colors
        return {
            color: '#475569',
            backgroundColor: '#f1f5f9',
            borderColor: '#cbd5e1'
        };
    }
    const s = status.toLowerCase();
    if (s === 'full' || s === 'done') {
        // Fully Delivered - green
        return {
            color: '#077959',
            backgroundColor: '#ecfdf5',
            borderColor: '#a7f3d0'
        };
    }
    if (s === 'partial' || s === 'started' || s === 'pending') {
        // Partially Delivered - amber
        return {
            color: '#b5550c',
            backgroundColor: '#fffbeb',
            borderColor: '#fde68a'
        };
    }
    // Default - slate colors
    return {
        color: '#475569',
        backgroundColor: '#f1f5f9',
        borderColor: '#cbd5e1'
    };
};

const processingConfirmations = ref<Record<string, boolean>>({});

// Confirmation Modal State
const confirmModalOpen = ref(false);
const orderToConfirm = ref<any>(null);
const confirmActionType = ref<'confirm'|'unconfirm'>('confirm');

const initiateToggle = (order: any) => {
    if (!props.canConfirmCommission || !order.commission_calculation) return;
    
    orderToConfirm.value = order;
    confirmActionType.value = !!order.commission_calculation.confirmed_by_manager ? 'unconfirm' : 'confirm';
    confirmModalOpen.value = true;
};

const executeToggle = () => {
    const order = orderToConfirm.value;
    if (!order) return;

    const commissionId = order.commission_calculation.id;
    const isCurrentlyConfirmed = !!order.commission_calculation.confirmed_by_manager;
    
    processingConfirmations.value[commissionId] = true;
    confirmModalOpen.value = false;
    
    const routeName = isCurrentlyConfirmed ? 'commissions.reset-confirm' : 'commissions.confirm';
    
    router.post(route(routeName, commissionId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processingConfirmations.value[commissionId] = false;
            orderToConfirm.value = null;
        }
    });
};

// Entered to Odoo Modal State
const odooModalOpen = ref(false);
const orderToOdooMark = ref<any>(null);

const initiateOdooToggle = (order: any) => {
    if (!props.canMarkOdoo || !order.commission_calculation) return;
    
    // Only allow marking as entered, no un-marking according to current backend functionality
    if (order.commission_calculation.entered_to_odoo) return;
    
    orderToOdooMark.value = order;
    odooModalOpen.value = true;
};

const executeOdooToggle = () => {
    const order = orderToOdooMark.value;
    if (!order) return;

    const commissionId = order.commission_calculation.id;
    
    // Add to processing state (we can reuse processingConfirmations for the spinner)
    processingConfirmations.value[commissionId] = true;
    odooModalOpen.value = false;
    
    router.post(route('commissions.mark-odoo', commissionId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processingConfirmations.value[commissionId] = false;
            orderToOdooMark.value = null;
        }
    });
};

const page = usePage<any>();
const isAdmin = computed(() =>
    (page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Admin')
);
const isAccountOfficer = computed(() =>
    (page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Account Officer')
);
const isSalesPerson = computed(() =>
    !isAdmin.value && !isAccountOfficer.value &&
    !(page.props.auth.user?.roles as any[])?.some((role: any) => role.name === 'Sales Manager')
);
</script>

<template>
    <Head title="Sales Orders" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4 relative">
            <div class="sticky md:static top-0 z-20 flex items-center justify-between gap-2 flex-wrap bg-background/95 backdrop-blur pb-3 pt-1">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold tracking-tight">Sales Orders</h1>
                    <p v-if="viewScope" class="text-sm text-muted-foreground mt-1">
                        Viewing: {{ viewScope }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="relative w-full max-w-xs items-center">
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

                    <!-- Simplified Pagination -->
                    <div class="flex items-center gap-4">
                        <div class="text-sm font-medium text-muted-foreground whitespace-nowrap">
                            {{ salesOrders.from }} - {{ salesOrders.to }} / {{ salesOrders.total }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                class="size-8"
                                :disabled="!salesOrders.prev_page_url"
                                as-child
                            >
                                <Link 
                                    v-if="salesOrders.prev_page_url"
                                    :href="salesOrders.prev_page_url" 
                                    preserve-scroll
                                >
                                    <ChevronLeft class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronLeft class="size-4" />
                                </span>
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                class="size-8"
                                :disabled="!salesOrders.next_page_url"
                                as-child
                            >
                                <Link 
                                    v-if="salesOrders.next_page_url"
                                    :href="salesOrders.next_page_url" 
                                    preserve-scroll
                                >
                                    <ChevronRight class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronRight class="size-4" />
                                </span>
                            </Button>
                        </div>
                    </div>

                    <Sheet v-if="!isSalesPerson" v-model:open="isFilterSheetOpen">
                        <SheetTrigger as-child>
                            <Button variant="outline" class="gap-2">
                                <Filter class="size-4" />
                                Filters
                                <Badge v-if="getActiveFilterCount() > 0" variant="secondary" class="ml-1 h-5 px-1.5 text-[10px]">
                                    {{ getActiveFilterCount() }}
                                </Badge>
                            </Button>
                        </SheetTrigger>
                        <SheetContent class="sm:max-w-md overflow-y-auto p-0 flex flex-col">
                            <SheetHeader class="px-6 pt-6">
                                <SheetTitle>Filter Sales Orders</SheetTitle>
                                <SheetDescription>
                                    Select options below to filter the sales orders list.
                                </SheetDescription>
                            </SheetHeader>

                            <div class="grid gap-5 py-4 px-6">
                                <!-- Filter by Users Search -->
                                <div class="grid gap-4">
                                    <Label class="text-base font-semibold">Filter by Users</Label>
                                    <div class="relative">
                                        <div class="relative w-full items-center">
                                            <Input
                                                v-model="userSearchQuery"
                                                type="text"
                                                placeholder="Search users..."
                                                class="pl-10"
                                                @focus="isUserSearchOpen = true"
                                            />
                                            <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                                                <Search class="size-4 text-muted-foreground" />
                                            </span>
                                        </div>

                                        <!-- User Search Results Dropdown -->
                                        <div v-if="isUserSearchOpen && userSearchQuery" class="absolute z-10 w-full mt-1 bg-background border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <div 
                                                v-for="user in filteredUsers" 
                                                :key="user.id"
                                                class="px-4 py-2 hover:bg-accent cursor-pointer flex items-center justify-between"
                                                @click="addUser(user.id)"
                                            >
                                                <span>{{ user.name }}</span>
                                                <X v-if="selectedUserIds.includes(String(user.id))" class="size-3" />
                                            </div>
                                            <div v-if="filteredUsers.length === 0" class="px-4 py-2 text-muted-foreground text-sm">
                                                No users found.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected Users Badges -->
                                    <div v-if="selectedUserIds.length > 0" class="flex flex-wrap gap-2 items-center">
                                        <Badge 
                                            v-for="id in displayedUserIds" 
                                            :key="id"
                                            variant="secondary"
                                            class="gap-1 px-2 py-1"
                                        >
                                            {{ props.users.find(u => String(u.id) === id)?.name || (id === 'pending' ? 'Pending Mapping' : '') }}
                                            <button 
                                                type="button"
                                                class="ml-1 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 hover:bg-muted p-0.5"
                                                @click.stop="removeUser(id)"
                                            >
                                                <X class="size-3" />
                                                <span class="sr-only">Remove user</span>
                                            </button>
                                        </Badge>
                                        <span v-if="othersCount > 0" class="text-xs text-muted-foreground italic">
                                            + {{ othersCount }} others available
                                        </span>
                                    </div>
                                </div>

                                <Separator class="my-1" />

                                <!-- Quick Actions (Shortcuts) -->
                                <div class="grid gap-3">
                                    <Label class="text-base font-semibold">Quick Actions (Shortcuts)</Label>
                                    <div class="flex flex-wrap gap-2">
                                        <Button 
                                            :variant="isPayableCommissionsActive ? 'default' : 'outline'" 
                                            size="sm" 
                                            class="text-xs transition-colors" 
                                            :class="isPayableCommissionsActive ? 'bg-blue-600 hover:bg-blue-700 text-white border-blue-600' : ''"
                                            @click="setPayableCommissionsFilter"
                                        >
                                            Payable Commissions
                                        </Button>
                                        <Button 
                                            :variant="isPayableCommissionsAccountingActive ? 'default' : 'outline'" 
                                            size="sm" 
                                            class="text-xs transition-colors" 
                                            :class="isPayableCommissionsAccountingActive ? 'bg-blue-600 hover:bg-blue-700 text-white border-blue-600' : ''"
                                            @click="setPayableCommissionsAccountingFilter"
                                        >
                                            Payable Commissions - Accounting
                                        </Button>
                                    </div>
                                </div>

                                <Separator class="my-1" />

                                <!-- Commission Status -->
                                <div class="grid gap-4 mt-2">
                                    <Label class="text-base font-semibold">Commission Status</Label>
                                    <div class="grid gap-2">
                                        <div v-for="option in commissionOptions" :key="option" class="flex items-center space-x-2">
                                            <Checkbox 
                                                :id="'comm-' + option" 
                                                :model-value="commissionStatus.includes(option)"
                                                @update:model-value="toggleFilter(commissionStatus, option)"
                                            />
                                            <label :for="'comm-' + option" class="text-sm font-medium leading-none cursor-pointer">
                                                {{ option }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Manager Confirmation Status -->
                                <div class="grid gap-4 mt-2">
                                    <Label class="text-base font-semibold">Manager Confirmation</Label>
                                    <div class="grid gap-2">
                                        <div v-for="option in managerConfirmationOptions" :key="option" class="flex items-center space-x-2">
                                            <Checkbox 
                                                :id="'manconf-' + option" 
                                                :model-value="managerConfirmationStatus.includes(option)"
                                                @update:model-value="toggleFilter(managerConfirmationStatus, option)"
                                            />
                                            <label :for="'manconf-' + option" class="text-sm font-medium leading-none cursor-pointer">
                                                {{ option }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Status -->
                                <div class="grid gap-4 mt-2">
                                    <Label class="text-base font-semibold">Invoice Payment Status</Label>
                                    <div class="grid gap-2">
                                        <div v-for="option in invoiceOptions" :key="option" class="flex items-center space-x-2">
                                            <Checkbox 
                                                :id="'inv-' + option" 
                                                :model-value="invoiceStatus.includes(option)"
                                                @update:model-value="toggleFilter(invoiceStatus, option)"
                                            />
                                            <label :for="'inv-' + option" class="text-sm font-medium leading-none cursor-pointer">
                                                {{ option }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Status -->
                                <div class="grid gap-4 mt-2">
                                    <Label class="text-base font-semibold">Delivery Status</Label>
                                    <div class="grid gap-2">
                                        <div v-for="option in deliveryOptions" :key="option" class="flex items-center space-x-2">
                                            <Checkbox 
                                                :id="'del-' + option" 
                                                :model-value="deliveryStatus.includes(option)"
                                                @update:model-value="toggleFilter(deliveryStatus, option)"
                                            />
                                            <label :for="'del-' + option" class="text-sm font-medium leading-none cursor-pointer">
                                                {{ option }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date Range Filter -->
                                <div class="grid gap-4 mt-2">
                                    <Label class="text-base font-semibold">Order Date Range</Label>
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        class="w-full justify-start text-left font-normal h-10 px-3"
                                                        :class="!dateFrom && 'text-muted-foreground'"
                                                    >
                                                        <Calendar class="mr-2 size-4" />
                                                        {{ displayDate(dateFrom) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0 mx-4" align="start">
                                                    <CalendarPicker
                                                        :model-value="parseCalendarDate(dateFrom)"
                                                        initial-focus
                                                        @update:model-value="(v) => dateFrom = v ? v.toString() : ''"
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                        <span class="text-muted-foreground text-sm">to</span>
                                        <div class="flex-1">
                                            <Popover>
                                                <PopoverTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        class="w-full justify-start text-left font-normal h-10 px-3"
                                                        :class="!dateTo && 'text-muted-foreground'"
                                                    >
                                                        <Calendar class="mr-2 size-4" />
                                                        {{ displayDate(dateTo) }}
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent class="w-auto p-0 mx-4" align="start">
                                                    <CalendarPicker
                                                        :model-value="parseCalendarDate(dateTo)"
                                                        initial-focus
                                                        @update:model-value="(v) => dateTo = v ? v.toString() : ''"
                                                    />
                                                </PopoverContent>
                                            </Popover>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <SheetFooter class="flex flex-row justify-between items-center gap-2 mt-auto pt-6 border-t px-6 pb-6">
                                <Button variant="ghost" class="text-primary hover:text-primary/90 hover:bg-primary/5 px-2" @click="resetFilters">
                                    Reset Filters
                                </Button>
                                <div class="flex gap-2">
                                    <Button variant="outline" @click="isFilterSheetOpen = false">Cancel</Button>
                                    <Button variant="default" class="px-6" @click="applyFilters">Apply Filters</Button>
                                </div>
                            </SheetFooter>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>

            <!-- ── Mobile List Tiles (< md) ─────────────────────────── -->
            <div class="md:hidden flex flex-col gap-2">
                <div
                    v-if="salesOrders.data.length === 0"
                    class="py-16 text-center text-muted-foreground text-sm"
                >
                    No results.
                </div>
                <div
                    v-for="order in salesOrders.data"
                    :key="order.id"
                    class="rounded-xl border bg-card px-4 py-3 shadow-sm active:bg-muted/40 transition-colors"
                >
                    <!-- Title row -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex-1 min-w-0">
                            <Link :href="route('sales-orders.show', order.id)" class="font-semibold text-sm hover:underline">
                                {{ order.name }}
                            </Link>
                            <p class="text-xs text-muted-foreground truncate mt-0.5">{{ order.partner_name }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <Badge
                                variant="outline"
                                class="text-[10px] border"
                                :style="getDeliveryBadgeStyles(order.delivery_status)"
                            >
                                {{ formatDeliveryStatus(order.delivery_status) }}
                            </Badge>
                            <span
                                v-if="order.x_studio_commission_paid"
                                class="text-[10px] text-emerald-600 font-medium"
                            >✓ Comm. Paid</span>
                            <span v-else class="text-[10px] text-muted-foreground">Comm. Unpaid</span>
                        </div>
                    </div>

                    <!-- Details grid -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Date</span>
                            <span class="font-medium">{{ formatDate(order.create_date) }}</span>
                        </div>
                        <div class="flex justify-between" v-if="order.installation_date && order.has_installation">
                            <span class="text-muted-foreground text-blue-600 font-semibold">Est. Install Date</span>
                            <span class="font-medium text-blue-600">{{ formatDate(order.installation_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Total</span>
                            <span class="font-medium">{{ formatCurrency(order.amount_total) }}</span>
                        </div>
                        <div class="flex justify-between col-span-2">
                            <span class="text-muted-foreground">Salesperson</span>
                            <span class="font-medium">{{ order.user_name || '-' }}</span>
                        </div>
                        <div class="flex justify-between col-span-2" v-if="order.commission_calculation">
                            <span class="text-muted-foreground">Commission</span>
                            <span
                                class="font-medium"
                                :class="order.commission_calculation.final_commission > 0 ? 'text-emerald-600' : 'text-red-500'"
                            >
                                {{ formatCurrency(order.commission_calculation.final_commission) }}
                            </span>
                        </div>
                        <div class="flex justify-between col-span-2" v-if="order.commission_calculation">
                            <span class="text-muted-foreground">Source</span>
                            <Badge
                                variant="outline"
                                class="text-[10px] border py-0"
                                :style="getBadgeStyles(order.commission_calculation.sales_source)"
                            >
                                {{ formatSource(order.commission_calculation.sales_source) }}
                            </Badge>
                        </div>
                    </div>
                    
                    <!-- Quick Manager Action (Mobile) -->
                    <div v-if="props.canConfirmCommission && order.commission_calculation" class="mt-3 pt-3 border-t border-border flex justify-end">
                        <Button 
                            v-if="!order.commission_calculation.confirmed_by_manager"
                            variant="outline" 
                            size="sm" 
                            class="h-7 text-xs border-emerald-600/30 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800"
                            :disabled="processingConfirmations[order.commission_calculation.id]"
                            @click="initiateToggle(order)"
                        >
                            <Loader2 v-if="processingConfirmations[order.commission_calculation.id]" class="size-3 mr-1.5 animate-spin" />
                            <span v-else class="mr-1.5 font-bold">✓</span>
                            Confirm
                        </Button>
                        <Button 
                            v-else
                            variant="outline" 
                            size="sm" 
                            class="h-7 text-xs text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                            :disabled="processingConfirmations[order.commission_calculation.id]"
                            @click="initiateToggle(order)"
                        >
                            <Loader2 v-if="processingConfirmations[order.commission_calculation.id]" class="size-3 mr-1.5 animate-spin" />
                            <span v-else class="mr-1.5">✗</span>
                            Unconfirm
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ── Desktop Table (≥ md) ─────────────────────────────── -->
            <div class="hidden md:block rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Order #</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead class="text-blue-600 font-semibold">Est. Install Date</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead v-if="canViewInstaller">Installer</TableHead>
                            <TableHead>Salesperson</TableHead>
                            <TableHead>Commission Owner</TableHead>
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
                            <TableCell class="font-bold text-blue-600 whitespace-nowrap">
                                <div v-if="order.installation_date && order.has_installation" class="flex items-center gap-1.5">
                                    <Calendar class="size-3.5" />
                                    {{ formatDate(order.installation_date) }}
                                </div>
                                <span v-else class="text-muted-foreground/30 font-normal">-</span>
                            </TableCell>
                            <TableCell>{{ order.partner_name }}</TableCell>
                            <TableCell v-if="canViewInstaller">
                                <span v-if="order.installer_name">{{ order.installer_name }}</span>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>{{ order.user_name }}</TableCell>
                            <TableCell>
                                <div v-if="order.commission_calculation?.user" class="font-medium">
                                    {{ order.commission_calculation.user.name }}
                                </div>
                                <span v-else class="text-muted-foreground text-xs italic">Pending Mapping</span>
                            </TableCell>
                            <TableCell>
                                <Badge 
                                    v-if="order.commission_calculation" 
                                    variant="outline" 
                                    class="whitespace-nowrap border"
                                    :style="getBadgeStyles(order.commission_calculation.sales_source)"
                                >
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
                                    <template v-if="order.commission_calculation">
                                        <div v-if="processingConfirmations[order.commission_calculation.id]" class="flex justify-center items-center h-4 w-4">
                                            <Loader2 class="size-3.5 animate-spin text-muted-foreground" />
                                        </div>
                                        <Checkbox 
                                            v-else
                                            :model-value="!!order.commission_calculation.confirmed_by_manager" 
                                            :disabled="!props.canConfirmCommission"
                                            :class="props.canConfirmCommission ? 'cursor-pointer' : ''"
                                            @click.prevent="props.canConfirmCommission ? initiateToggle(order) : null"
                                        />
                                    </template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-center">
                                    <template v-if="order.commission_calculation">
                                        <div v-if="processingConfirmations[order.commission_calculation.id]" class="flex justify-center items-center h-4 w-4">
                                            <Loader2 class="size-3.5 animate-spin text-muted-foreground" />
                                        </div>
                                        <Checkbox 
                                            v-else
                                            :model-value="!!order.commission_calculation.entered_to_odoo" 
                                            :disabled="!props.canMarkOdoo || !!order.commission_calculation.entered_to_odoo"
                                            :class="props.canMarkOdoo && !order.commission_calculation.entered_to_odoo ? 'cursor-pointer' : ''"
                                            @click.prevent="props.canMarkOdoo && !order.commission_calculation.entered_to_odoo ? initiateOdooToggle(order) : null"
                                        />
                                    </template>
                                    <span v-else class="text-muted-foreground">-</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge 
                                    variant="outline" 
                                    class="whitespace-nowrap border text-xs font-medium"
                                    :style="getDeliveryBadgeStyles(order.delivery_status)"
                                >
                                    {{ formatDeliveryStatus(order.delivery_status) }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ formatCurrency(order.amount_total) }}</TableCell>
                            <TableCell>
                                <span 
                                    v-if="order.commission_calculation" 
                                    class="font-medium"
                                    :class="order.commission_calculation.final_commission > 0 ? 'text-emerald-600' : 'text-red-600'"
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
                            <TableCell :colspan="canViewInstaller ? 15 : 14" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

        </div>
        <!-- Confirmation Modal -->
        <Dialog :open="confirmModalOpen" @update:open="confirmModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        {{ confirmActionType === 'confirm' ? 'Confirm Commission' : 'Unconfirm Commission' }}
                    </DialogTitle>
                    <DialogDescription v-if="orderToConfirm">
                        Are you sure you want to {{ confirmActionType === 'confirm' ? 'confirm' : 'unconfirm' }} the commission for order <strong>{{ orderToConfirm.name }}</strong>?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="sm:justify-end gap-2 sm:gap-0">
                    <Button variant="outline" @click="confirmModalOpen = false">
                        Cancel
                    </Button>
                    <Button 
                        :variant="confirmActionType === 'confirm' ? 'default' : 'destructive'" 
                        @click="executeToggle"
                    >
                        {{ confirmActionType === 'confirm' ? 'Yes, Confirm' : 'Yes, Unconfirm' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Entered to Odoo Modal -->
        <Dialog :open="odooModalOpen" @update:open="odooModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Mark as Entered to Odoo</DialogTitle>
                    <DialogDescription v-if="orderToOdooMark">
                        Are you sure you want to mark the commission for order <strong>{{ orderToOdooMark.name }}</strong> as entered in Odoo?
                        <br/><br/>
                        <span class="text-xs text-muted-foreground">Note: This action cannot be undone directly from the UI.</span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="sm:justify-end gap-2 sm:gap-0">
                    <Button variant="outline" @click="odooModalOpen = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="default"
                        @click="executeOdooToggle"
                    >
                        Yes, Mark Entered
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
