<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
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
import MonthlyBarChart from '@/components/charts/MonthlyBarChart.vue';
import { ref } from 'vue';
import { Filter } from 'lucide-vue-next';

const props = defineProps<{
    monthly: Array<{ month: string; label: string; total_sales: number; total_profit: number }>;
    totals: { total_sales: number; total_profit: number };
    users: Array<{ id: number; name: string }>;
    filters: { user_ids: number[] };
}>();

const selectedUserIds = ref<number[]>(props.filters.user_ids ?? []);
const isFilterOpen = ref(false);

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

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
        maximumFractionDigits: 0,
    }).format(amount);
};

const salesChartData = props.monthly.map((m) => ({ label: m.label, value: m.total_sales }));
const profitChartData = props.monthly.map((m) => ({ label: m.label, value: m.total_profit }));
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

            <Card>
                <CardContent class="pt-6">
                    <MonthlyBarChart :data="salesChartData" title="Total Sales By Month" color="#0891b2" />
                </CardContent>
            </Card>

            <Card>
                <CardContent class="pt-6">
                    <MonthlyBarChart :data="profitChartData" title="Total Profit By Month" color="#059669" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
