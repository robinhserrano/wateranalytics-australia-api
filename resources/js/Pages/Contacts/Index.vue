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
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    contacts: {
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
    };
}>();

const search = ref(props.filters.search || '');

const handleSearch = useDebounceFn((value: string) => {
    router.get(
        route('contacts.index'),
        { search: value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => {
    handleSearch(value);
});
</script>

<template>
    <Head title="Contacts" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Contacts</h1>
                <div class="flex items-center gap-4">
                    <!-- Simplified Pagination -->
                    <div class="flex items-center gap-4">
                        <div class="text-sm font-medium text-muted-foreground whitespace-nowrap">
                            {{ contacts.from }} - {{ contacts.to }} / {{ contacts.total }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                class="size-8"
                                :disabled="!contacts.prev_page_url"
                                as-child
                            >
                                <Link 
                                    v-if="contacts.prev_page_url"
                                    :href="contacts.prev_page_url" 
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
                                :disabled="!contacts.next_page_url"
                                as-child
                            >
                                <Link 
                                    v-if="contacts.next_page_url"
                                    :href="contacts.next_page_url" 
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

                    <div class="relative w-full max-w-sm items-center">
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search contacts..."
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
                            <TableHead>Name</TableHead>
                            <TableHead>City</TableHead>
                            <TableHead>Zip</TableHead>
                            <TableHead>Parent Company</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="contact in contacts.data"
                            :key="contact.id"
                        >
                            <TableCell class="font-medium">
                                {{ contact.display_name }}
                            </TableCell>
                            <TableCell>{{ contact.city || '-' }}</TableCell>
                            <TableCell>{{ contact.zip || '-' }}</TableCell>
                            <TableCell>{{ contact.parent_name || '-' }}</TableCell>
                            <TableCell class="text-right">
                                <Button variant="ghost" size="sm" as-child>
                                    <Link :href="route('contacts.show', contact.id)">
                                        View
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="contacts.data.length === 0">
                            <TableCell colspan="5" class="h-24 text-center">
                                No results.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

        </div>
    </AppLayout>
</template>
