<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { UserPlus, UserMinus } from 'lucide-vue-next';
import { getRoleStyle } from '@/lib/utils';
import { ref } from 'vue';

const props = defineProps<{
    team: any;
    managers: any[];
    availableUsers: any[];
}>();

interface TeamForm {
    name: string;
    team_manager_id: number | null;
    description: string;
    is_active: boolean;
}

const form = useForm<TeamForm>({
    name: props.team.name,
    team_manager_id: props.team.team_manager_id,
    description: props.team.description,
    is_active: props.team.is_active,
});

const selectedUserId = ref<number | null>(null);

const submit = () => {
    form.put(route('teams.update', props.team.id));
};

const addMember = () => {
    if (selectedUserId.value) {
        router.post(route('teams.members.add', props.team.id), {
            user_id: selectedUserId.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedUserId.value = null;
            },
        });
    }
};

const removeMember = (userId: number) => {
    if (confirm('Remove this member from the team?')) {
        router.delete(route('teams.members.remove', [props.team.id, userId]), {
            preserveScroll: true,
        });
    }
};

const availableUsersForAdd = () => {
    return props.availableUsers.filter(u => !u.team_id || u.team_id === props.team.id);
};
</script>

<template>
    <Head title="Edit Team" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Edit Team</h1>
            </div>

            <div class="grid gap-6 lg:grid-cols-10">
                <!-- Team Details Form -->
                <div class="lg:col-span-3 rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold">Team Details</h2>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Team Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="team_manager_id">Team Manager</Label>
                            <Select v-model="form.team_manager_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a manager" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">No Manager</SelectItem>
                                    <SelectItem
                                        v-for="manager in managers"
                                        :key="manager.id"
                                        :value="manager.id"
                                    >
                                        {{ manager.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="form.is_active = $event"
                            />
                            <Label for="is_active" class="cursor-pointer">
                                Active
                            </Label>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                Update Team
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="route('teams.index')">Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Team Members Management -->
                <div class="lg:col-span-7 rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold">Team Members</h2>
                    
                    <!-- Add Member -->
                    <div class="mb-4 flex gap-2">
                        <Select v-model="selectedUserId" class="flex-1">
                            <SelectTrigger>
                                <SelectValue placeholder="Select user to add" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="user in availableUsersForAdd()"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button @click="addMember" :disabled="!selectedUserId">
                            <UserPlus class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Members List -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead class="text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="member in team.members" :key="member.id">
                                    <TableCell class="font-medium">
                                        {{ member.name }}
                                    </TableCell>
                                    <TableCell>{{ member.email }}</TableCell>
                                    <TableCell>
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="role in member.roles" :key="role.name" variant="outline" class="text-[10px] px-1.5 py-0 h-5" :style="getRoleStyle(role.name)">
                                                {{ role.name }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="removeMember(member.id)"
                                        >
                                            <UserMinus class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="!team.members || team.members.length === 0">
                                    <TableCell colspan="4" class="h-24 text-center">
                                        No members in this team.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
