<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    logs: {
        data: Array<{
            id: number;
            command: string;
            status: string;
            started_at: string;
            completed_at: string | null;
            duration: number | null;
            records_processed: number;
            message: string | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Sync Logs',
        href: route('admin.logs.index'),
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
    });
};

const formatDuration = (seconds: number | null) => {
    if (seconds === null) return '-';
    if (seconds < 60) return `${seconds}s`;
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}m ${remainingSeconds}s`;
};

const getStatusClass = (status: string) => {
     switch (status) {
        case 'completed':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100';
        case 'failed':
            return 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100';
        case 'running':
            return 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 animate-pulse';
        default:
            return '';
    }
};
</script>

<template>
    <Head title="Odoo Sync Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Odoo Sync Logs</h1>
                    <p class="text-muted-foreground">
                        History of background synchronization tasks.
                    </p>
                </div>
                 <Button variant="outline" @click="router.reload()">Refresh Logs</Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Execution History</CardTitle>
                    <CardDescription>Latest sync command executions.</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Status</TableHead>
                                <TableHead>Command</TableHead>
                                <TableHead>Started At</TableHead>
                                <TableHead>Duration</TableHead>
                                <TableHead class="text-right">Records</TableHead>
                                <TableHead>Message</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id">
                                <TableCell>
                                    <Badge variant="outline" :class="getStatusClass(log.status)">
                                        {{ log.status.toUpperCase() }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="font-mono text-xs">{{ log.command }}</TableCell>
                                <TableCell>{{ formatDate(log.started_at) }}</TableCell>
                                <TableCell>{{ formatDuration(log.duration) }}</TableCell>
                                <TableCell class="text-right">{{ log.records_processed }}</TableCell>
                                <TableCell class="max-w-[300px] truncate" :title="log.message || ''">
                                    {{ log.message || '-' }}
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="logs.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No logs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <template v-for="(link, i) in logs.links" :key="i">
                            <Button 
                                v-if="link.url || link.active"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                :disabled="!link.url"
                                as-child
                            >
                                <Link v-if="link.url" :href="link.url"><span v-html="link.label"></span></Link>
                                <span v-else v-html="link.label"></span>
                            </Button>
                        </template>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
