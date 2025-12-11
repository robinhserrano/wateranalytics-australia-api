<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ArrowLeft, Package, DollarSign, Box, Tag, Edit, Plus, Receipt } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    product: any;
}>();

const dialogOpen = ref(false);

const form = useForm({
    installation_service: 0,
    supply_only: 0,
    effective_from: new Date().toISOString().split('T')[0],
    product_category: props.product.categ_name || '',
});

const submitLandingPrice = () => {
    form.post(route('products.landing-price.update', props.product.id), {
        onSuccess: () => {
            dialogOpen.value = false;
            form.reset();
        },
    });
};

// Get current landing price based on today's date
const currentLandingPrice = computed(() => {
    if (!props.product.landing_prices || props.product.landing_prices.length === 0) {
        return null;
    }
    
    const today = new Date();
    const validPrices = props.product.landing_prices
        .filter((price: any) => {
            if (!price.effective_from) return true;
            return new Date(price.effective_from) <= today;
        })
        .sort((a: any, b: any) => {
            const dateA = a.effective_from ? new Date(a.effective_from).getTime() : 0;
            const dateB = b.effective_from ? new Date(b.effective_from).getTime() : 0;
            return dateB - dateA;
        });
    
    return validPrices[0] || null;
});

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: 'AUD',
    }).format(amount);
};

const formatQty = (qty: number | null) => {
    if (qty === null) return '-';
    return new Intl.NumberFormat('en-AU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(qty);
};

const getTypeVariant = (type: string) => {
    switch (type) {
        case 'product':
            return 'default';
        case 'service':
            return 'secondary';
        case 'consu':
            return 'outline';
        default:
            return 'outline';
    }
};

const getTypeLabel = (type: string) => {
    switch (type) {
        case 'product':
            return 'Storable Product';
        case 'service':
            return 'Service';
        case 'consu':
            return 'Consumable';
        default:
            return type;
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-AU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDateOnly = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-AU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="product.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('products.index')">
                        <ArrowLeft class="size-4" />
                    </Link>
                </Button>
                <div class="flex items-center gap-2 flex-1">
                    <Package class="size-6" />
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ product.name }}
                    </h1>
                </div>
                <Badge v-if="product.type" :variant="getTypeVariant(product.type)" class="text-sm">
                    {{ getTypeLabel(product.type) }}
                </Badge>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Tag class="size-5" />
                            <CardTitle>Product Information</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">Odoo ID</div>
                            <div class="text-sm">{{ product.odoo_id }}</div>
                            
                            <div class="text-sm font-medium text-muted-foreground">Internal Reference</div>
                            <div class="text-sm">
                                <code v-if="product.default_code" class="text-xs bg-muted px-1.5 py-0.5 rounded">
                                    {{ product.default_code }}
                                </code>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>

                            <div class="text-sm font-medium text-muted-foreground">Category</div>
                            <div class="text-sm">{{ product.categ_name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Unit of Measure</div>
                            <div class="text-sm">{{ product.uom_name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Currency</div>
                            <div class="text-sm">{{ product.currency_name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Last Updated</div>
                            <div class="text-sm">{{ formatDate(product.write_date) }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <DollarSign class="size-5" />
                            <CardTitle>Pricing & Inventory</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">List Price</div>
                            <div class="text-sm font-semibold">{{ formatCurrency(product.list_price) }}</div>

                            <div class="text-sm font-medium text-muted-foreground">On Hand Quantity</div>
                            <div class="text-sm font-semibold">{{ formatQty(product.qty_available) }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Product Variants</div>
                            <div class="text-sm">
                                <Badge variant="outline">
                                    {{ product.product_variant_count }} variant{{ product.product_variant_count !== 1 ? 's' : '' }}
                                </Badge>
                            </div>

                            <div class="text-sm font-medium text-muted-foreground">Priority</div>
                            <div class="text-sm">
                                <Badge v-if="product.priority === '1'" variant="destructive">High</Badge>
                                <Badge v-else-if="product.priority === '2'" variant="default">Medium</Badge>
                                <Badge v-else-if="product.priority === '3'" variant="secondary">Low</Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>

                            <div class="text-sm font-medium text-muted-foreground">Activity State</div>
                            <div class="text-sm">
                                <Badge v-if="product.activity_state === 'overdue'" variant="destructive">Overdue</Badge>
                                <Badge v-else-if="product.activity_state === 'today'" variant="default">Today</Badge>
                                <Badge v-else-if="product.activity_state === 'planned'" variant="secondary">Planned</Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Landing Prices Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Receipt class="size-5" />
                                <div>
                                    <CardTitle>Landing Prices</CardTitle>
                                    <CardDescription>Manage installation and supply-only pricing</CardDescription>
                                </div>
                            </div>
                            <Dialog v-model:open="dialogOpen">
                                <DialogTrigger as-child>
                                    <Button>
                                        <Plus class="size-4 mr-2" />
                                        Add Landing Price
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[500px]">
                                    <DialogHeader>
                                        <DialogTitle>Add Landing Price</DialogTitle>
                                        <DialogDescription>
                                            Set the installation and supply-only prices with an effective date.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitLandingPrice" class="space-y-4">
                                        <div class="space-y-2">
                                            <Label for="effective_from">Effective From</Label>
                                            <Input
                                                id="effective_from"
                                                v-model="form.effective_from"
                                                type="date"
                                                required
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="product_category">Product Category</Label>
                                            <Input
                                                id="product_category"
                                                v-model="form.product_category"
                                                type="text"
                                                placeholder="e.g., System, Taps"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="installation_service">Installation Service Price (AUD)</Label>
                                            <Input
                                                id="installation_service"
                                                v-model.number="form.installation_service"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="supply_only">Supply Only Price (AUD)</Label>
                                            <Input
                                                id="supply_only"
                                                v-model.number="form.supply_only"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                            />
                                        </div>
                                        <DialogFooter>
                                            <Button type="button" variant="outline" @click="dialogOpen = false">
                                                Cancel
                                            </Button>
                                            <Button type="submit" :disabled="form.processing">
                                                Save Landing Price
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Current Landing Price -->
                        <div v-if="currentLandingPrice" class="mb-6 p-4 bg-muted rounded-lg">
                            <div class="text-sm font-medium text-muted-foreground mb-2">Current Pricing</div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <div class="text-xs text-muted-foreground">Effective From</div>
                                    <div class="text-sm font-semibold">{{ formatDateOnly(currentLandingPrice.effective_from) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-muted-foreground">Category</div>
                                    <div class="text-sm font-semibold">{{ currentLandingPrice.product_category || '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-muted-foreground">Installation Service</div>
                                    <div class="text-sm font-semibold">{{ formatCurrency(currentLandingPrice.installation_service) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-muted-foreground">Supply Only</div>
                                    <div class="text-sm font-semibold">{{ formatCurrency(currentLandingPrice.supply_only) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Price History -->
                        <div v-if="product.landing_prices && product.landing_prices.length > 0">
                            <div class="text-sm font-medium text-muted-foreground mb-3">Price History</div>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Effective From</TableHead>
                                            <TableHead>Category</TableHead>
                                            <TableHead class="text-right">Installation Service</TableHead>
                                            <TableHead class="text-right">Supply Only</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="price in product.landing_prices"
                                            :key="price.id"
                                        >
                                            <TableCell>{{ formatDateOnly(price.effective_from) }}</TableCell>
                                            <TableCell>{{ price.product_category || '-' }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(price.installation_service) }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(price.supply_only) }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No landing prices set for this product yet.
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="product.product_properties && Object.keys(product.product_properties).length > 0" class="md:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Box class="size-5" />
                            <CardTitle>Product Properties</CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="(value, key) in product.product_properties" :key="key" class="space-y-1">
                                <div class="text-sm font-medium text-muted-foreground capitalize">
                                    {{ String(key).replace(/_/g, ' ') }}
                                </div>
                                <div class="text-sm">{{ value || '-' }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
