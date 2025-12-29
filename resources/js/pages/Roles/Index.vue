<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Trash2, Edit, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        permissions: Array<{ name: string }>;
    }>;
    permissions: Array<{
        id: number;
        name: string;
    }>;
}>();

// Role Deletion
const deleteRole = (id: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(route('roles.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Permission Creation
const isPermissionDialogOpen = ref(false);
const permissionForm = useForm({
    name: '',
});

const createPermission = () => {
    permissionForm.post(route('permissions.store'), {
        onSuccess: () => {
            isPermissionDialogOpen.value = false;
            permissionForm.reset();
        },
    });
};

// Permission Deletion
const deletePermission = (id: number) => {
    if (confirm('Are you sure you want to delete this permission? It will be removed from all assigned roles.')) {
        router.delete(route('permissions.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Roles & Permissions" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Roles & Permissions</h2>
                    <p class="text-muted-foreground">Manage user roles and system permissions.</p>
                </div>
            </div>

            <Tabs default-value="roles" class="w-full">
                <TabsList>
                    <TabsTrigger value="roles">Roles</TabsTrigger>
                    <TabsTrigger value="permissions">Permissions</TabsTrigger>
                </TabsList>

                <!-- ROLES TAB -->
                <TabsContent value="roles" class="space-y-4">
                    <div class="flex justify-end">
                        <Button as-child>
                            <Link :href="route('roles.create')">Create Role</Link>
                        </Button>
                    </div>
                    <Card>
                        <CardHeader>
                            <CardTitle>All Roles</CardTitle>
                            <CardDescription>
                                A list of all defined roles in the system.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Role Name</TableHead>
                                        <TableHead>Permissions</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="role in roles" :key="role.id">
                                        <TableCell class="font-medium">
                                            {{ role.name }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-wrap gap-1">
                                                <Badge v-for="permission in role.permissions" :key="permission.name" variant="secondary" class="text-xs">
                                                    {{ permission.name }}
                                                </Badge>
                                                <span v-if="!role.permissions.length" class="text-muted-foreground text-sm">-</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button variant="ghost" size="icon" as-child>
                                                    <Link :href="route('roles.edit', role.id)">
                                                        <Edit class="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button 
                                                    variant="ghost" 
                                                    size="icon" 
                                                    class="text-red-500 hover:text-red-700 hover:bg-red-50"
                                                    @click="deleteRole(role.id)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="roles.length === 0">
                                        <TableCell colspan="3" class="text-center py-8 text-muted-foreground">
                                            No roles found. Create one to get started.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- PERMISSIONS TAB -->
                <TabsContent value="permissions" class="space-y-4">
                     <div class="flex justify-end">
                        <Dialog v-model:open="isPermissionDialogOpen">
                            <DialogTrigger as-child>
                                <Button>
                                    <Plus class="mr-2 h-4 w-4" /> Create Permission
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-[425px]">
                                <DialogHeader>
                                    <DialogTitle>Create Permission</DialogTitle>
                                    <DialogDescription>
                                        Add a new permission to the system. This name should match the permission check code (e.g. 'delete_users').
                                    </DialogDescription>
                                </DialogHeader>
                                <form @submit.prevent="createPermission">
                                    <div class="grid gap-4 py-4">
                                        <div class="grid grid-cols-4 items-center gap-4">
                                            <Label for="name" class="text-right">
                                                Name
                                            </Label>
                                            <Input
                                                id="name"
                                                v-model="permissionForm.name"
                                                class="col-span-3"
                                                placeholder="e.g. view_reports"
                                                required
                                            />
                                        </div>
                                        <div v-if="permissionForm.errors.name" class="text-right text-sm text-red-500 col-span-4">
                                            {{ permissionForm.errors.name }}
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button type="submit" :disabled="permissionForm.processing">
                                            Save permission
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Available Permissions</CardTitle>
                            <CardDescription>
                                A list of all permissions available to be assigned to roles.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                             <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Permission Name</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="permission in permissions" :key="permission.id">
                                        <TableCell class="font-medium">
                                            {{ permission.name }}
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button 
                                                variant="ghost" 
                                                size="icon" 
                                                class="text-red-500 hover:text-red-700 hover:bg-red-50"
                                                @click="deletePermission(permission.id)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="permissions.length === 0">
                                        <TableCell colspan="2" class="text-center py-8 text-muted-foreground">
                                            No permissions found.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
