<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableEmpty } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { ExternalLink, Edit, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { format } from 'date-fns';
import { getRoleStyle } from '@/lib/utils';
import { computed, ref } from 'vue';

interface TeamMember {
    id: number;
    name: string;
    email: string;
    initials: string;
    role: string;
    team: string;
    is_active: boolean;
    latest_sale_date: string | null;
}

const props = defineProps<{
    members: TeamMember[];
    canEdit: boolean;
    preview: {
        teamId: number;
        teamName: string;
        managerName: string | null;
        message: string | null;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = props.preview
    ? [
          { title: 'Teams', href: route('teams.index') },
          { title: 'View as manager', href: route('teams.view-as-manager', props.preview.teamId) },
      ]
    : [
          {
              title: 'Dashboard',
              href: route('dashboard'),
          },
          {
              title: 'My Team',
              href: route('my-team.index'),
          },
      ];

const latestSaleSort = ref<'asc' | 'desc'>('desc');

/** Same comma-separated format as backend `roles->implode(', ')`. */
const parseRoleNames = (role: string) =>
    role
        .split(',')
        .map((r) => r.trim())
        .filter(Boolean);

const formatLatestSale = (dateString: string | null) => {
    if (!dateString) return '—';
    try {
        return format(new Date(dateString), 'MMM d, yyyy');
    } catch {
        return dateString;
    }
};

/** Active if latest sale is within the last 6 months (same rule as Users). */
const isSalesActive = (latestSaleDate: string | null) => {
    if (!latestSaleDate) return false;
    const date = new Date(latestSaleDate);
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    return date > sixMonthsAgo;
};

const sortedMembers = computed(() => {
    const members = [...props.members];

    members.sort((a, b) => {
        const aTime = a.latest_sale_date ? new Date(a.latest_sale_date).getTime() : null;
        const bTime = b.latest_sale_date ? new Date(b.latest_sale_date).getTime() : null;

        if (aTime === null && bTime === null) return 0;
        if (aTime === null) return 1;
        if (bTime === null) return -1;

        return latestSaleSort.value === 'asc' ? aTime - bTime : bTime - aTime;
    });

    return members;
});

const toggleLatestSaleSort = () => {
    latestSaleSort.value = latestSaleSort.value === 'asc' ? 'desc' : 'asc';
};
</script>

<template>
    <Head :title="preview ? `My Team — ${preview.teamName}` : 'My Team'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">My Team</h1>
                    <p class="text-muted-foreground">
                        <template v-if="preview">
                            Preview of what the team manager sees on My Team.
                        </template>
                        <template v-else>
                            View and manage your team members.
                        </template>
                    </p>
                </div>
                <Button v-if="preview" variant="outline" as-child>
                    <Link :href="route('teams.index')">Back to Teams</Link>
                </Button>
            </div>

            <div
                v-if="preview"
                class="rounded-lg border border-dashed bg-muted/40 p-4 text-sm"
            >
                <p class="font-medium">
                    Team: {{ preview.teamName }}
                </p>
                <p v-if="preview.managerName" class="text-muted-foreground mt-1">
                    Shown as: <span class="text-foreground font-medium">{{ preview.managerName }}</span>
                </p>
                <p v-if="preview.message" class="text-muted-foreground mt-2">
                    {{ preview.message }}
                </p>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Member</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Team</TableHead>
                            <TableHead>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 text-left hover:text-foreground"
                                    @click="toggleLatestSaleSort"
                                >
                                    Latest sale
                                    <ArrowUp
                                        v-if="latestSaleSort === 'asc'"
                                        class="size-3.5 text-muted-foreground"
                                    />
                                    <ArrowDown
                                        v-else
                                        class="size-3.5 text-muted-foreground"
                                    />
                                </button>
                            </TableHead>
                            <TableHead>Sales activity</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="member in sortedMembers" :key="member.id" class="hover:bg-muted/50 transition-colors">
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
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="(roleName, idx) in parseRoleNames(member.role)"
                                        :key="`${member.id}-${idx}-${roleName}`"
                                        variant="outline"
                                        class="text-[10px] px-2 py-0"
                                        :style="getRoleStyle(roleName)"
                                    >
                                        {{ roleName }}
                                    </Badge>
                                    <span
                                        v-if="parseRoleNames(member.role).length === 0"
                                        class="text-muted-foreground"
                                    >
                                        -
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span class="text-sm">{{ member.team }}</span>
                            </TableCell>
                            <TableCell>
                                <span class="text-sm text-muted-foreground">{{ formatLatestSale(member.latest_sale_date) }}</span>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center">
                                    <Badge
                                        :variant="isSalesActive(member.latest_sale_date) ? 'default' : 'secondary'"
                                        class="text-[10px] px-2 py-0"
                                    >
                                        {{ isSalesActive(member.latest_sale_date) ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
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
                        <TableEmpty v-if="members.length === 0" :colspan="6">
                             No team members found.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
