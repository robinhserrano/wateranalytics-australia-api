<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableEmpty } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { ExternalLink, Edit } from 'lucide-vue-next';

interface TeamMember {
    id: number;
    name: string;
    email: string;
    initials: string;
    role: string;
    team: string;
    is_active: boolean;
}

const props = defineProps<{
    members: TeamMember[];
    canEdit: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'My Team',
        href: route('my-team.index'),
    },
];

const getRoleBadgeVariant = (role: string) => {
    if (role.includes('Manager')) return 'default';
    if (role.includes('Admin')) return 'destructive';
    return 'secondary';
};
</script>

<template>
    <Head title="My Team" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">My Team</h1>
                    <p class="text-muted-foreground">
                        View and manage your team members.
                    </p>
                </div>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Member</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Team</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="member in members" :key="member.id" class="hover:bg-muted/50 transition-colors">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <Avatar class="h-9 w-9 border">
                                        <AvatarFallback>{{ member.initials }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-sm">{{ member.name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ member.email }}</span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getRoleBadgeVariant(member.role)" class="text-[10px] px-2 py-0">
                                    {{ member.role || 'No Role' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <span class="text-sm">{{ member.team }}</span>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" class="text-[10px] px-2 py-0" v-if="!member.is_active">
                                    Inactive
                                </Badge>
                                <Badge variant="secondary" class="text-[10px] px-2 py-0" v-else>
                                    Active
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1">
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0" as-child title="Sales Orders">
                                        <Link :href="route('sales-orders.index', { user_ids: [member.id] })">
                                            <ExternalLink class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button 
                                        v-if="canEdit" 
                                        variant="ghost" 
                                        size="sm" 
                                        class="h-8 w-8 p-0" 
                                        as-child
                                        title="Edit Profile"
                                    >
                                        <Link :href="route('users.edit', member.id)">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableEmpty v-if="members.length === 0" :colspan="5">
                             No team members found.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
