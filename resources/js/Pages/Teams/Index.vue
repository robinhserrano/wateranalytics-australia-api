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
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { getRoleStyle } from '@/lib/utils';
import { Users, Plus, Edit, Trash2, Eye, Network, ArrowRight } from 'lucide-vue-next';
import { format } from 'date-fns';

const props = defineProps<{
    teams: any[];
}>();

const deleteTeam = (teamId: number) => {
    if (confirm('Are you sure you want to delete this team?')) {
        router.delete(route('teams.destroy', teamId));
    }
};

const getInitials = (name: string) => {
    if (!name) return '';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const formatLatestSale = (dateString: string | null) => {
    if (!dateString) return 'No sales recorded';
    try {
        return 'Last sale: ' + format(new Date(dateString), 'MMM d, yyyy');
    } catch {
        return 'Last sale: ' + dateString;
    }
};
</script>

<template>
    <Head title="Teams" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Teams</h1>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('teams.hierarchy')">
                            <Network class="mr-2 h-4 w-4" />
                            View Hierarchy
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('teams.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Team
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Team Name</TableHead>
                            <TableHead>Team Manager</TableHead>
                            <TableHead>Members</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="team in teams" :key="team.id">
                            <TableCell class="font-medium">
                                {{ team.name }}
                            </TableCell>
                             <TableCell>
                                <div class="flex items-center gap-2.5">
                                    <template v-if="team.manager_hierarchy">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Avatar class="h-6 w-6 border shadow-sm shrink-0 cursor-help">
                                                        <AvatarFallback 
                                                            :style="getRoleStyle(team.manager_hierarchy.role)"
                                                            class="text-[9px] font-bold"
                                                        >
                                                            {{ team.manager_hierarchy.initials }}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                </TooltipTrigger>
                                                <TooltipContent px-2 py-1>
                                                    <p class="text-xs font-medium">{{ team.manager_hierarchy.name }}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                        <ArrowRight class="h-3.5 w-3.5 text-muted-foreground/50 shrink-0" />
                                    </template>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-semibold text-sm truncate">
                                            {{ team.team_manager?.name || '-' }}
                                        </span>
                                        <span class="text-[10px] text-muted-foreground leading-tight truncate">
                                            {{ team.team_manager?.role || '-' }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Users class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ team.members_count || 0 }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-1">
                                    <Badge :variant="team.is_active && team.is_sales_active ? 'default' : 'secondary'" class="w-fit">
                                        {{ team.is_active && team.is_sales_active ? 'Active' : (team.is_active ? 'Inactive (No recent sales)' : 'Deactivated') }}
                                    </Badge>
                                    <span class="text-[10px] text-muted-foreground leading-none mt-0.5">
                                        {{ formatLatestSale(team.latest_sale_date) }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="sm" as-child title="View as team manager">
                                        <Link :href="route('teams.view-as-manager', team.id)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('teams.edit', team.id)">
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="sm"
                                        @click="deleteTeam(team.id)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="teams.length === 0">
                            <TableCell colspan="5" class="h-24 text-center">
                                No teams found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
