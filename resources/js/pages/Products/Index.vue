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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search, Package } from 'lucide-vue-next';

const props = defineProps<{
    products: {
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
    categories: any[];
    filters: {
        search?: string;
        category_id?: string;
        type?: string;
    };
}>();

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '0');
const productType = ref(props.filters.type || 'all');

const handleSearch = useDebounceFn(() => {
    router.get(
        route('products.index'),
        { 
            search: search.value,
            category_id: categoryId.value,
            type: productType.value,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch([search, categoryId, productType], () => {
    handleSearch();
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
</script>

<template>
    <Head title="Products" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Package class="h-6 w-6" />
                    <h1 class="text-2xl font-bold tracking-tight">Products</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Select :model-value="categoryId" @update:model-value="(value) => categoryId = value as string">
                        <SelectTrigger class="w-[200px]">
                            <SelectValue placeholder="All Categories" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">All Categories</SelectItem>
                            <SelectItem 
                                v-for="category in categories" 
                                :key="category.categ_id"
                                :value="category.categ_id.toString()"
                            >
                                {{ category.categ_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    
                    <Select :model-value="productType" @update:model-value="(value) => productType = value as string">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            <SelectItem value="product">Storable Product</SelectItem>
                            <SelectItem value="service">Service</SelectItem>
                            <SelectItem value="consu">Consumable</SelectItem>
                        </SelectContent>
                    </Select>
                    
                    <div class="relative w-full max-w-sm items-center">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search products..."
                            class="pl-10"
                        />
                        <span
                            class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
                        >
                            <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Product Name</TableHead>
                            <TableHead>Internal Reference</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead class="text-right">List Price</TableHead>
                            <TableHead class="text-right">On Hand Qty</TableHead>
                            <TableHead class="text-right">Variants</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="product in products.data"
                            :key="product.id"
                        >
                            <TableCell class="font-medium">
                                {{ product.name }}
                            </TableCell>
                            <TableCell>
                                <code v-if="product.default_code" class="text-xs bg-muted px-1.5 py-0.5 rounded">
                                    {{ product.default_code }}
                                </code>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell>{{ product.categ_name || '-' }}</TableCell>
                            <TableCell>
                                <Badge v-if="product.type" :variant="getTypeVariant(product.type)">
                                    {{ getTypeLabel(product.type) }}
                                </Badge>
                                <span v-else class="text-muted-foreground">-</span>
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatCurrency(product.list_price) }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ formatQty(product.qty_available) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <Badge variant="outline">
                                    {{ product.product_variant_count }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('products.show', product.id)">
                                        View
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="products.data.length === 0">
                            <TableCell colspan="8" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex items-center justify-between py-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
                </div>
                <div class="flex items-center space-x-2">
                    <template v-for="(link, index) in products.links" :key="index">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            as-child
                        >
                            <Link :href="link.url" preserve-scroll>
                                <span v-html="link.label"></span>
                            </Link>
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            disabled
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
