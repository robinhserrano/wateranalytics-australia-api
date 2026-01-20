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
import { Separator } from '@/components/ui/separator';
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
import { Search, Filter, X, RotateCcw, ChevronDown, Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next';

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
        user_ids?: string[];
        date_from?: string;
        date_to?: string;
    };
    users: Array<{ id: number; name: string }>;
    viewScope?: string;
}>();

const search = ref(props.filters.search || '');

const ensureArray = (val: any): string[] => {
    if (!val) return [];
    return Array.isArray(val) ? val : [val];
};

const commissionStatus = ref<string[]>(ensureArray(props.filters.commission_status));
const invoiceStatus = ref<string[]>(ensureArray(props.filters.invoice_status));
const deliveryStatus = ref<string[]>(ensureArray(props.filters.delivery_status));
const selectedUserIds = ref<string[]>(ensureArray(props.filters.user_ids));
const dateFrom = ref<string>(props.filters.date_from || '');
const dateTo = ref<string>(props.filters.date_to || '');

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
            user_ids: selectedUserIds.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true }
    );
    isFilterSheetOpen.value = false;
};

const resetFilters = () => {
    commissionStatus.value = [];
    invoiceStatus.value = [];
    deliveryStatus.value = [];
    selectedUserIds.value = [];
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

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

                    <Sheet v-model:open="isFilterSheetOpen">
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

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Order #</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Customer</TableHead>
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
                            <TableCell>{{ order.partner_name }}</TableCell>
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
                            <TableCell colspan="14" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

        </div>
    </AppLayout>
</template>
