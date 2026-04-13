<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ScrollArea } from '@/components/ui/scroll-area';

const props = defineProps<{
    contacts: Array<{
        odoo_id: number;
        display_name: string;
        user_id: number | null;
        odoo_user_ids: number[];
    }>;
    roles: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role_name: '',
    commission_split: 0,
    company_lead_base: 0,
    self_gen_base: 0,
    legacy_id: null as number | null,
    contact_ids: [] as number[],
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset('password'),
    });
};

const isContactSelected = (odooId: string | number) => {
    return form.contact_ids.includes(Number(odooId));
};

const toggleContact = (odooId: string | number) => {
    const id = Number(odooId);
    if (form.contact_ids.includes(id)) {
        form.contact_ids = form.contact_ids.filter(existingId => existingId !== id);
    } else {
        form.contact_ids = [...form.contact_ids, id];
    }
};

const searchQuery = ref('');

const filteredContacts = computed(() => {
    if (!searchQuery.value) {
        return props.contacts;
    }
    const query = searchQuery.value.toLowerCase();
    return props.contacts.filter(contact => 
        contact.display_name.toLowerCase().includes(query)
    );
});
const selectedUserIds = computed(() => {
    return props.contacts
        .filter(c => form.contact_ids.includes(Number(c.odoo_id)))
        .map(c => c.odoo_user_ids?.[0])
        .filter(Boolean);
});
</script>

<template>
    <Head title="Create User" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4 max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold tracking-tight">Create User</h2>

            <form @submit.prevent="submit">
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>User Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Display Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="form.email" required />
                                <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                            </div>

                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <Input id="password" type="password" v-model="form.password" required />
                                <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                            </div>

                            <div class="space-y-2">
                                <Label for="role">Role</Label>
                                <Select v-model="form.role_name">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                            {{ role.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="form.errors.role_name" class="text-red-500 text-sm">{{ form.errors.role_name }}</span>
                            </div>

                            <div class="space-y-2">
                                <Label for="legacy_id">Legacy ID (Optional)</Label>
                                <Input id="legacy_id" type="number" :modelValue="form.legacy_id ?? undefined" @update:modelValue="val => form.legacy_id = val ? Number(val) : null" placeholder="V1 User ID" />
                                <span v-if="form.errors.legacy_id" class="text-red-500 text-sm">{{ form.errors.legacy_id }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Commission Settings</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="commission_split">Commission Split (%)</Label>
                                <Input id="commission_split" type="number" min="0" max="100" step="0.01" v-model="form.commission_split" />
                                <span v-if="form.errors.commission_split" class="text-red-500 text-sm">{{ form.errors.commission_split }}</span>
                            </div>
                            <div class="space-y-2">
                                <Label for="company_lead_base">Company Lead Base</Label>
                                <Input id="company_lead_base" type="number" min="0" step="0.01" v-model="form.company_lead_base" />
                                <span v-if="form.errors.company_lead_base" class="text-red-500 text-sm">{{ form.errors.company_lead_base }}</span>
                            </div>
                             <div class="space-y-2">
                                <Label for="self_gen_base">Self Gen Base</Label>
                                <Input id="self_gen_base" type="number" min="0" step="0.01" v-model="form.self_gen_base" />
                                <span v-if="form.errors.self_gen_base" class="text-red-500 text-sm">{{ form.errors.self_gen_base }}</span>
                            </div>
                        </CardContent>
                    </Card>

                     <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Assign Contacts</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="mb-4 flex justify-between items-center">
                                <Input 
                                    placeholder="Search contacts..." 
                                    v-model="searchQuery"
                                    class="max-w-xs"
                                />
                                <span class="text-sm text-muted-foreground">
                                    Selected: {{ form.contact_ids.length }}
                                </span>
                            </div>
                            
                            <!-- Debug Block (Minimal) -->
                            <div v-if="selectedUserIds.length > 0" class="mb-2 text-xs text-muted-foreground">
                                IDs: {{ selectedUserIds.join(', ') }}
                            </div>

                            <ScrollArea class="h-[300px] w-full border rounded-md p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div 
                                        v-for="contact in filteredContacts" 
                                        :key="contact.odoo_id" 
                                        class="flex items-center space-x-2 cursor-pointer hover:bg-slate-50 p-1 rounded"
                                        @click="toggleContact(Number(contact.odoo_id))"
                                    >
                                        <Checkbox 
                                            :id="`contact-${contact.odoo_id}`" 
                                            :checked="isContactSelected(contact.odoo_id)"
                                            @update:checked="() => toggleContact(contact.odoo_id)"
                                        />
                                        <span
                                            :for="`contact-${contact.odoo_id}`"
                                            class="text-sm font-medium leading-none cursor-pointer pointer-events-none"
                                        >
                                            [{{ contact.odoo_user_ids?.[0] || 'N/A' }}] {{ contact.display_name }} 
                                            <span v-if="!contact.email || contact.email.trim() === ''" title="This is a ghost contact (portal login only or no email)" class="text-xs ml-1">👻 Ghost</span>
                                            <span v-if="contact.user_id" class="text-xs text-muted-foreground ml-1">
                                                (Assigned to another user)
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </ScrollArea>
                             <span v-if="form.errors.contact_ids" class="text-red-500 text-sm">{{ form.errors.contact_ids }}</span>
                        </CardContent>
                    </Card>
                </div>

                <div class="flex justify-end mt-4">
                    <Button type="submit" :disabled="form.processing">
                        Create User
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
