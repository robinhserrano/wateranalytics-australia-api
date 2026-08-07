<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ArrowLeft, Calendar, User, MapPin, RefreshCw, AlertCircle, Loader2, File, ExternalLink } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

const props = defineProps<{
    task: {
        id: number;
        name: string;
        partner_name: string;
        user_name: string | null;
        installer_name: string | null;
        installation_date: string | null;
        odoo_task_id: number | null;
        lines: any[];
        partner: any | null;
    };
}>();

// ─── Messages state ───────────────────────────────────────────────────────────

interface Author {
    id: number;
    type: string;
}

interface TrackingValue {
    id: number;
    changedField: string;
    fieldName: string;
    fieldType: string;
    newValue: { value: any };
    oldValue: { value: any };
}

interface Attachment {
    id: number;
    name: string;
    filename: string;
    mimetype: string;
    size: number;
    checksum: string;
}

interface Message {
    id: number;
    author: Author;
    body: string;
    date: string;
    is_note: boolean;
    is_discussion: boolean;
    message_type: string;
    attachment_ids: number[];
    trackingValues: TrackingValue[];
    record_name: string;
}

interface Partner {
    id: number;
    name: string;
    userId: number;
    isInternalUser: boolean;
}

const messages = ref<Message[]>([]);
const partners = ref<Record<number, Partner>>({});
const attachments = ref<Record<number, Attachment>>({});
const loadingMessages = ref(false);
const messagesError = ref<string | null>(null);

// Group messages by day (Newest first)
const groupedMessages = computed(() => {
    const groups: Record<string, Message[]> = {};
    [...messages.value]
        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
        .forEach((msg) => {
            const day = new Date(msg.date).toLocaleDateString('en-AU', {
                year: 'numeric', month: 'long', day: 'numeric'
            });
            if (!groups[day]) groups[day] = [];
            groups[day].push(msg);
        });
    return groups;
});

const fetchMessages = async () => {
    if (!props.task.odoo_task_id) {
        messagesError.value = 'No Odoo task linked to this installation order.';
        return;
    }

    loadingMessages.value = true;
    messagesError.value = null;

    try {
        const response = await fetch(route('installation-tasks.messages', props.task.id));
        const data = await response.json();

        if (data.error) {
            messagesError.value = data.error;
            return;
        }

        const result = data.result;
        const msgList: Message[] = (result?.messages ?? []).map((id: number) => {
            const mails = result?.data?.['mail.message'] ?? [];
            return mails.find((m: Message) => m.id === id);
        }).filter(Boolean);

        messages.value = msgList;

        // Build partner lookup
        const partnerList: Partner[] = result?.data?.['res.partner'] ?? [];
        partnerList.forEach((p) => { partners.value[p.id] = p; });

        // Build attachment lookup
        const attachList: Attachment[] = result?.data?.['ir.attachment'] ?? [];
        attachList.forEach((a) => { attachments.value[a.id] = a; });

    } catch {
        messagesError.value = 'Failed to load messages. Please try again.';
    } finally {
        loadingMessages.value = false;
    }
};

onMounted(fetchMessages);

// ─── Helpers ───────────────────────────────────────────────────────────────────

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-AU', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('en-AU', { hour: '2-digit', minute: '2-digit' });
};

const getAuthorName = (author: Author) => {
    const p = partners.value[author.id];
    return p?.name ?? 'Unknown';
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const getAuthorColor = (authorId: number) => {
    const colors = [
        'bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-rose-500',
        'bg-amber-500', 'bg-teal-500', 'bg-indigo-500', 'bg-pink-500',
    ];
    return colors[authorId % colors.length];
};

const isImageAttachment = (att: Attachment) => att.mimetype?.startsWith('image/');

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
};

const formatCurrency = (amount: number | null) => {
    if (amount === null) return '-';
    return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(amount);
};
</script>

<template>
    <Head :title="`Installation — ${task.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <!-- Back button + title -->
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('installation-tasks.index')">
                        <ArrowLeft class="size-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">{{ task.name }}</h1>
                    <p class="text-sm text-muted-foreground">Installation Task</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">

                <!-- ── LEFT: Order Details ─────────────────────────────── -->
                <div class="lg:col-span-1 flex flex-col gap-4">
                    <Card>
                        <CardContent class="grid gap-3 pt-6">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                <Calendar class="size-3.5" />
                                INSTALLATION DETAILS
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Est. Install Date</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">
                                        {{ formatDate(task.installation_date) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Salesperson</span>
                                    <span class="font-medium">{{ task.user_name || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Installer</span>
                                    <span class="font-medium">{{ task.installer_name || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Odoo Task</span>
                                    <span class="font-medium text-xs text-muted-foreground/60">
                                        {{ task.odoo_task_id ? `#${task.odoo_task_id}` : 'Not linked' }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="grid gap-3 pt-6">
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                <User class="size-3.5" />
                                CUSTOMER
                            </div>
                            <div>
                                <h3 class="font-bold text-base">{{ task.partner_name }}</h3>
                                <a
                                    v-if="task.partner?.phone"
                                    :href="'tel:' + task.partner.phone"
                                    class="text-blue-600 text-sm hover:underline block mt-0.5"
                                >{{ task.partner.phone }}</a>
                            </div>
                            <div v-if="task.partner?.contact_address_complete" class="flex gap-2 text-muted-foreground text-sm">
                                <MapPin class="size-4 shrink-0 mt-0.5" />
                                <p>{{ task.partner.contact_address_complete }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Order lines -->
                    <Card v-if="task.lines?.length">
                        <CardContent class="pt-6">
                            <div class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Order Lines</div>
                            <div class="space-y-2">
                                <div
                                    v-for="line in task.lines"
                                    :key="line.id"
                                    class="flex justify-between text-sm border-b pb-2 last:border-0 last:pb-0"
                                >
                                    <span class="text-muted-foreground text-xs pr-3 leading-relaxed">{{ line.product_name || line.name }}</span>
                                    <span class="font-medium whitespace-nowrap">{{ formatCurrency(line.price_subtotal) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- ── RIGHT: Chatter / Log Notes ─────────────────────── -->
                <div class="lg:col-span-2">
                    <Card class="h-full">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center justify-between text-base">
                                <span>Log Notes</span>
                                <Button variant="outline" size="sm" class="h-8 gap-1.5" @click="fetchMessages" :disabled="loadingMessages">
                                    <Loader2 v-if="loadingMessages" class="size-3.5 animate-spin" />
                                    <RefreshCw v-else class="size-3.5" />
                                    Refresh
                                </Button>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>

                            <!-- Loading -->
                            <div v-if="loadingMessages" class="flex items-center justify-center py-12 gap-2 text-muted-foreground">
                                <Loader2 class="size-5 animate-spin" />
                                <span class="text-sm">Loading messages from Odoo...</span>
                            </div>

                            <!-- Error -->
                            <div v-else-if="messagesError" class="flex items-start gap-3 p-4 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300">
                                <AlertCircle class="size-4 mt-0.5 shrink-0" />
                                <span class="text-sm">{{ messagesError }}</span>
                            </div>

                            <!-- Empty state -->
                            <div v-else-if="messages.length === 0" class="text-center py-12 text-muted-foreground text-sm">
                                No log notes found for this task.
                            </div>

                            <!-- Messages grouped by date -->
                            <div v-else class="space-y-8">
                                <div v-for="(dayMessages, day) in groupedMessages" :key="day">

                                    <!-- Day divider -->
                                    <div class="flex items-center gap-3 my-4">
                                        <div class="flex-1 border-t border-border/60"></div>
                                        <span class="text-xs font-semibold text-muted-foreground px-2">{{ day }}</span>
                                        <div class="flex-1 border-t border-border/60"></div>
                                    </div>

                                    <div class="space-y-5">
                                        <div v-for="msg in dayMessages" :key="msg.id" class="flex gap-3">

                                            <!-- Avatar -->
                                            <div
                                                class="size-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5"
                                                :class="getAuthorColor(msg.author.id)"
                                            >
                                                {{ getInitials(getAuthorName(msg.author)) }}
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <!-- Header row -->
                                                <div class="flex items-baseline gap-2 mb-1.5">
                                                    <span class="font-semibold text-sm">{{ getAuthorName(msg.author) }}</span>
                                                    <span class="text-xs text-muted-foreground">{{ formatTime(msg.date) }}</span>
                                                    <span
                                                        v-if="msg.is_note"
                                                        class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-medium"
                                                    >Note</span>
                                                    <span
                                                        v-else-if="msg.message_type === 'notification'"
                                                        class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 font-medium"
                                                    >Activity</span>
                                                </div>

                                                <!-- Body text -->
                                                <div
                                                    v-if="msg.body"
                                                    class="text-sm leading-relaxed text-foreground prose dark:prose-invert max-w-none"
                                                    v-html="msg.body"
                                                ></div>

                                                <!-- Tracking values (e.g. Low → High) -->
                                                <div v-if="msg.trackingValues?.length" class="mt-2 space-y-1">
                                                    <div
                                                        v-for="tv in msg.trackingValues"
                                                        :key="tv.id"
                                                        class="flex items-center gap-2 text-xs text-muted-foreground"
                                                    >
                                                        <span class="font-medium text-foreground">{{ tv.changedField }}</span>
                                                        <span>{{ tv.oldValue?.value ?? '—' }}</span>
                                                        <span class="text-muted-foreground/50">→</span>
                                                        <span class="font-semibold text-foreground">{{ tv.newValue?.value ?? '—' }}</span>
                                                    </div>
                                                </div>

                                                <!-- Attachments -->
                                                <div v-if="msg.attachment_ids?.length" class="mt-3 flex flex-wrap gap-2">
                                                    <template v-for="attId in msg.attachment_ids" :key="attId">
                                                        <div v-if="attachments[attId]">
                                                            <!-- Image preview -->
                                                            <Dialog v-if="isImageAttachment(attachments[attId])">
                                                                <DialogTrigger as-child>
                                                                    <div class="relative group rounded-lg overflow-hidden border border-border cursor-pointer hover:ring-2 hover:ring-primary/50 transition-all">
                                                                        <img
                                                                            :src="route('installation-tasks.attachment', attId)"
                                                                            :alt="attachments[attId].name"
                                                                            class="max-w-[220px] max-h-[160px] object-cover block"
                                                                            @error="(e: Event) => ((e.target as HTMLElement).style.display = 'none')"
                                                                        />
                                                                        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
                                                                        <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[10px] px-2 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity truncate backdrop-blur-sm">
                                                                            {{ attachments[attId].filename }}
                                                                        </div>
                                                                    </div>
                                                                </DialogTrigger>
                                                                <DialogContent class="sm:max-w-3xl border-border bg-background p-0 overflow-hidden">
                                                                    <DialogHeader class="p-4 border-b border-border bg-muted/30">
                                                                        <DialogTitle class="text-base truncate pr-6">{{ attachments[attId].filename }}</DialogTitle>
                                                                    </DialogHeader>
                                                                    <div class="p-6 flex flex-col items-center justify-center gap-6 bg-muted/10">
                                                                        <div class="rounded-md overflow-hidden border border-border shadow-sm max-w-full bg-black/5 dark:bg-white/5 flex items-center justify-center p-2">
                                                                            <img 
                                                                                :src="route('installation-tasks.attachment', attId)" 
                                                                                class="max-h-[65vh] object-contain rounded" 
                                                                                :alt="attachments[attId].filename"
                                                                            />
                                                                        </div>
                                                                        <Button as-child variant="outline" class="gap-2">
                                                                            <a :href="`https://wateranalytics.odoo.com/web/image/${attId}`" target="_blank">
                                                                                <ExternalLink class="size-4" />
                                                                                Open Original Image
                                                                            </a>
                                                                        </Button>
                                                                    </div>
                                                                </DialogContent>
                                                            </Dialog>

                                                            <!-- File badge -->
                                                            <div
                                                                v-else
                                                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-border bg-muted/30 text-xs"
                                                            >
                                                                <File class="size-3.5 text-muted-foreground" />
                                                                <span class="max-w-[160px] truncate">{{ attachments[attId].filename }}</span>
                                                                <span class="text-muted-foreground/60">{{ formatFileSize(attachments[attId].size) }}</span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </CardContent>
                    </Card>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
