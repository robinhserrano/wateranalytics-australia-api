<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import {
    ExternalLink,
    ChevronLeft
} from 'lucide-vue-next';
import { format } from 'date-fns';

const props = defineProps<{
    user: {
        id: number; name: string; email: string; roles: Array<{ name: string }>;
        commission_split: number;
        company_lead_base: number;
        self_gen_base: number;
        is_active: boolean;
        contacts: Array<{ id: number; odoo_id: number; display_name: string; odoo_user_ids: string[] }>;
    };
    commissionStats: {
        is_active: boolean;
        latest_sale: {
            name: string;
            date: string;
            amount: number;
        } | null;
    };
    salesOrders: Array<{
        id: number;
        name: string;
        create_date: string;
        amount_total: number;
        state: string;
        x_studio_invoice_payment_status: string;
    }>;
}>();

const page = usePage();

const isAdmin = computed(() => {
    return (page.props.auth.user as any)?.roles?.some((role: any) => role.name === 'Admin');
});

const deleteUser = () => {
    router.delete(route('users.destroy', props.user.id), {
        onSuccess: () => {
            // Redirect handled by backend
        },
    });
};
</script>

<template>

    <Head :title="user.name" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" size="icon" as-child class="-ml-2">
                            <Link :href="route('users.index')">
                                <ChevronLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <h2 class="text-2xl font-bold tracking-tight">{{ user.name }}</h2>
                    </div>
                    <p class="text-muted-foreground">{{ user.email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('users.edit', user.id)">Edit User</Link>
                    </Button>

                    <Dialog v-if="isAdmin && (page.props.auth.user as any).id !== user.id">
                        <DialogTrigger as-child>
                            <Button variant="destructive">
                                Delete User
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Delete User</DialogTitle>
                                <DialogDescription>
                                    Are you sure you want to delete <strong>{{ user.name }}</strong>? This action cannot
                                    be undone and will remove all associated data.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button variant="outline">Cancel</Button>
                                </DialogClose>
                                <Button variant="destructive" @click="deleteUser">Delete User</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Roles:</span>
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="role in user.roles" :key="role.name" variant="outline">
                                    {{ role.name }}
                                </Badge>
                                <span v-if="!user.roles || user.roles.length === 0">-</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status:</span>
                            <Badge :variant="commissionStats.is_active ? 'default' : 'secondary'">
                                {{ commissionStats.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Commission Split:</span>
                            <span class="font-medium">{{ user.commission_split }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Company Lead Base:</span>
                            <span class="font-medium">${{ user.company_lead_base }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Self Gen Base:</span>
                            <span class="font-medium">${{ user.self_gen_base }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Assigned Contacts</CardTitle>
                        <CardDescription>Contacts assigned to this user for commission purposes.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="user.contacts.length" class="space-y-2">
                            <div v-for="contact in user.contacts" :key="contact.id"
                                class="flex items-center justify-between p-2 border rounded-md">
                                <span class="font-medium">{{ contact.display_name }}</span>
                                <span
                                    class="ml-2 px-2 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-gray-100 text-gray-800">
                                    Odoo User ID: {{ contact.odoo_user_ids?.[0] }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No contacts assigned.
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Sales Order History -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Order History</CardTitle>
                            <CardDescription>Recent sales orders where this user is the salesperson.</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Order Name</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Total Amount</TableHead>
                                    <TableHead>Odoo State</TableHead>
                                    <TableHead>Payment Status</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="order in salesOrders" :key="order.id">
                                    <TableCell class="font-medium">{{ order.name }}</TableCell>
                                    <TableCell>{{ format(new Date(order.create_date), 'MMM dd, yyyy') }}</TableCell>
                                    <TableCell>${{ Number(order.amount_total).toLocaleString() }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" class="capitalize">
                                            {{ order.state?.replace('_', ' ') || '-' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="order.x_studio_invoice_payment_status === 'paid' ? 'default' : 'secondary'">
                                            {{ order.x_studio_invoice_payment_status || 'not_paid' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('sales-orders.show', order.id)">
                                                <ExternalLink class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="salesOrders.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                                        No sales orders found for this salesperson.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
