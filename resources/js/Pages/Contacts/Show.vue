<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    contact: any;
}>();
</script>

<template>
    <Head :title="contact.display_name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('contacts.index')">
                        <ArrowLeft class="size-4" />
                    </Link>
                </Button>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ contact.display_name }}
                </h1>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Contact Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">Odoo ID</div>
                            <div class="text-sm">{{ contact.odoo_id }}</div>
                            
                            <div class="text-sm font-medium text-muted-foreground">Parent Company</div>
                            <div class="text-sm">{{ contact.parent_name || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Address</div>
                            <div class="text-sm whitespace-pre-wrap">{{ contact.contact_address_complete || '-' }}</div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Address Breakdown</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-sm font-medium text-muted-foreground">Street</div>
                            <div class="text-sm">{{ contact.street || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Street 2</div>
                            <div class="text-sm">{{ contact.street2 || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">City</div>
                            <div class="text-sm">{{ contact.city || '-' }}</div>

                            <div class="text-sm font-medium text-muted-foreground">Zip</div>
                            <div class="text-sm">{{ contact.zip || '-' }}</div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
