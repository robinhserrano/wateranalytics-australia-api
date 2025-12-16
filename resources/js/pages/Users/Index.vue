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

defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role?: { name: string };
            commission_split: number;
            is_active: boolean;
        }>;
        links: any[];
    };
}>();
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
                <Button as-child>
                    <Link :href="route('users.create')">Create User</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Users</CardTitle>
                    <CardDescription>
                        A list of all users including their name, role, and commission settings.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
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
                                    <Badge variant="outline" v-if="user.role">
                                        {{ user.role.name }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>{{ user.commission_split }}%</TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('users.show', user.id)">
                                            View
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
