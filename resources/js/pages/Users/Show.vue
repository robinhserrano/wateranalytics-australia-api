<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role?: { name: string };
        commission_split: number;
        company_lead_base: number;
        self_gen_base: number;
        is_active: boolean;
        contacts: Array<{
            id: number;
            display_name: string;
        }>;
    };
    commissionStats: {
        total_commissions: number;
        pending_amount: number;
        total_earned: number;
        this_month_earned: number;
    };
}>();
</script>

<template>
    <Head :title="user.name" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">{{ user.name }}</h2>
                    <p class="text-muted-foreground">{{ user.email }}</p>
                </div>
                <Button as-child>
                    <Link :href="route('users.edit', user.id)">Edit User</Link>
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Role:</span>
                            <span class="font-medium">{{ user.role?.name || '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status:</span>
                            <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Commission Split:</span>
                            <span class="font-medium">{{ user.commission_split }}%</span>
                        </div>
                         <div class="flex justify-between">
                            <span class="text-muted-foreground">Company Lead Base:</span>
                            <span class="font-medium">${{ user.company_lead_base }}</span>
                        </div>
                         <div class="flex justify-between">
                            <span class="text-muted-foreground">Self Gen Base:</span>
                            <span class="font-medium">${{ user.self_gen_base }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Assigned Contacts</CardTitle>
                        <CardDescription>Contacts assigned to this user for commission purposes.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="user.contacts.length" class="space-y-2">
                            <div v-for="contact in user.contacts" :key="contact.id" class="flex items-center justify-between p-2 border rounded-md">
                                <span class="font-medium">{{ contact.display_name }}</span>
                                <span class="ml-2 px-2 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-gray-100 text-gray-800">
                ID: {{ contact.id }}
            </span>
                            </div>
                        </div>
                        <div v-else class="text-muted-foreground">
                            No contacts assigned.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
