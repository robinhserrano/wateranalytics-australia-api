<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        permissions: Array<{ name: string }>;
    };
    permissions: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.name),
});

const submit = () => {
    form.put(route('roles.update', props.role.id), {
        onSuccess: () => {}, // Stay on page or redirect? Usually back to index is fine which put redirects to.
    });
};

const togglePermission = (permissionName: string, checked: boolean) => {
    console.log('togglePermission', permissionName, checked, typeof checked);
    if (checked) {
        form.permissions.push(permissionName);
    } else {
        form.permissions = form.permissions.filter(p => p !== permissionName);
    }
    console.log('Updated permissions:', form.permissions);
};
</script>

<template>
    <Head title="Edit Role" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4 max-w-2xl mx-auto">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('roles.index')">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <h2 class="text-2xl font-bold tracking-tight">Edit Role</h2>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Role Details</CardTitle>
                        <CardDescription>Update the role name and assigned permissions.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="e.g. Sales Manager"
                                required 
                            />
                            <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                        </div>

                        <div class="space-y-4">
                            <Label>Permissions</Label>
                            
                            <div v-if="permissions.length === 0" class="text-sm text-muted-foreground italic">
                                No permissions defined in the system.
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div 
                                    v-for="permission in permissions" 
                                    :key="permission.id" 
                                    class="flex items-center space-x-2"
                                >
                                    <Checkbox 
                                        :id="`perm-${permission.id}`" 
                                        :checked="form.permissions.includes(permission.name)"
                                        @update:checked="(checked: boolean) => togglePermission(permission.name, checked)"
                                    />
                                    <label
                                        :for="`perm-${permission.id}`"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                            <!-- DEBUG INFO: REMOVE AFTER FIXING -->
                            <div class="mt-4 p-2 bg-gray-100 rounded text-xs font-mono">
                                <strong>DEBUG State:</strong> {{ form.permissions }}
                            </div>
                            <span v-if="form.errors.permissions" class="text-red-500 text-sm">{{ form.errors.permissions }}</span>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            Save Changes
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
