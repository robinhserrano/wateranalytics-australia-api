<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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

defineProps<{
    managers: any[];
}>();

interface TeamForm {
    name: string;
    team_manager_id: number | null;
    description: string;
    is_active: boolean;
}

const form = useForm<TeamForm>({
    name: '',
    team_manager_id: null,
    description: '',
    is_active: true,
});

const submit = () => {
    form.post(route('teams.store'));
};
</script>

<template>
    <Head title="Create Team" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">Create Team</h1>
            </div>

            <div class="max-w-2xl rounded-lg border bg-card p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="name">Team Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Enter team name"
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
                        <p v-if="form.errors.team_manager_id" class="text-sm text-destructive">
                            {{ form.errors.team_manager_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Enter team description"
                            rows="4"
                        />
                        <p v-if="form.errors.description" class="text-sm text-destructive">
                            {{ form.errors.description }}
                        </p>
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
                            Create Team
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="route('teams.index')">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
