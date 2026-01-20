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
import { Users, Plus, Edit, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    teams: any[];
}>();

const deleteTeam = (teamId: number) => {
    if (confirm('Are you sure you want to delete this team?')) {
        router.delete(route('teams.destroy', teamId));
    }
};
</script>

<template>
    <Head title="Teams" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Teams</h1>
                <Button as-child>
                    <Link :href="route('teams.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Team
                    </Link>
                </Button>
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
                                {{ team.team_manager?.name || '-' }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Users class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ team.members_count || 0 }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="team.is_active ? 'default' : 'secondary'">
                                    {{ team.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
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
