<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
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
import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { ChevronLeft, ChevronRight, Trash2, Pencil } from 'lucide-vue-next';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            roles: Array<{ name: string }>;
            commission_split: number;
            is_active: boolean;
            contacts: Array<{ id: number; odoo_id: number; display_name: string }>;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        from: number;
        to: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search: string;
    };
}>();

const page = usePage();
const search = ref(props.filters.search || '');

const isAdmin = computed(() => {
    return (page.props.auth.user as any)?.roles?.some((role: any) => role.name === 'Admin');
});

const deleteUser = (id: number) => {
    router.delete(route('users.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            // Flash message handled by backend
        },
    });
};

watch(search, debounce((value) => {
    router.get(route('users.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>

    <Head title="Users" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Users</h2>
                    <p class="text-muted-foreground">Manage system users and their permissions.</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Simplified Pagination -->
                    <div class="flex items-center gap-4">
                        <div class="text-sm font-medium text-muted-foreground whitespace-nowrap">
                            {{ users.from }} - {{ users.to }} / {{ users.total }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Button variant="outline" size="icon" class="size-8" :disabled="!users.prev_page_url"
                                as-child>
                                <Link v-if="users.prev_page_url" :href="users.prev_page_url" preserve-scroll>
                                    <ChevronLeft class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronLeft class="size-4" />
                                </span>
                            </Button>
                            <Button variant="outline" size="icon" class="size-8" :disabled="!users.next_page_url"
                                as-child>
                                <Link v-if="users.next_page_url" :href="users.next_page_url" preserve-scroll>
                                    <ChevronRight class="size-4" />
                                </Link>
                                <span v-else>
                                    <ChevronRight class="size-4" />
                                </span>
                            </Button>
                        </div>
                    </div>

                    <Button as-child>
                        <Link :href="route('users.create')">Create User</Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>All Users</CardTitle>
                            <CardDescription>
                                A list of all users including their name, role, and commission settings.
                            </CardDescription>
                        </div>
                        <div class="w-full max-w-sm">
                            <Input v-model="search" placeholder="Search by name or email..." class="h-9" />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Contacts</TableHead>
                                <TableHead>Commission Split</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell class="font-medium">
                                    {{ user.name }}
                                </TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="role in user.roles" :key="role.name" variant="outline">
                                            {{ role.name }}
                                        </Badge>
                                        <span v-if="!user.roles || user.roles.length === 0"
                                            class="text-muted-foreground">-</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                        <TooltipProvider v-for="contact in user.contacts" :key="contact.id">
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Badge variant="secondary" class="cursor-help text-[10px] px-1.5 py-0 h-5">
                                                        {{ getInitials(contact.display_name) }}:{{ contact.odoo_user_ids?.[0] }}
                                                    </Badge>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>{{ contact.display_name }}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <span v-if="!user.contacts || user.contacts.length === 0" class="text-muted-foreground text-xs">-</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ user.commission_split }}%</TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('users.show', user.id)">
                                                View
                                            </Link>
                                        </Button>

                                        <Button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-foreground" as-child>
                                            <Link :href="route('users.edit', user.id)">
                                                <Pencil class="size-4" />
                                            </Link>
                                        </Button>

                                        <Dialog v-if="isAdmin && (page.props.auth.user as any).id !== user.id">
                                            <DialogTrigger as-child>
                                                <Button variant="ghost" size="icon"
                                                    class="size-8 text-destructive hover:text-destructive hover:bg-destructive/10">
                                                    <Trash2 class="size-4" />
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Delete User</DialogTitle>
                                                    <DialogDescription>
                                                        Are you sure you want to delete <strong>{{ user.name
                                                            }}</strong>? This action cannot be undone and will remove
                                                        all associated data.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter>
                                                    <DialogClose as-child>
                                                        <Button variant="outline">Cancel</Button>
                                                    </DialogClose>
                                                    <Button variant="destructive" @click="deleteUser(user.id)">Delete
                                                        User</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>
