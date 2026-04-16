<script setup lang="ts">
import { Handle, Position } from '@vue-flow/core';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { getRoleStyle } from '@/lib/utils';

interface UserNodeProps {
    data: {
        name: string;
        initials: string;
        role: string;
        team: string | null;
    };
}

defineProps<UserNodeProps>();
</script>

<template>
    <div class="px-4 py-3 shadow-md rounded-xl bg-card border border-border min-w-[200px] hover:border-primary/50 transition-all group">
        <Handle type="target" :position="Position.Top" class="!bg-muted-foreground/30 !w-3 !h-3" />
        
        <div class="flex items-center gap-3">
            <Avatar class="h-10 w-10 border shadow-sm group-hover:scale-105 transition-transform">
                <AvatarFallback 
                    :style="getRoleStyle(data.role)"
                    class="text-xs font-bold"
                >
                    {{ data.initials }}
                </AvatarFallback>
            </Avatar>
            
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-semibold truncate">{{ data.name }}</span>
                <span class="text-[10px] text-muted-foreground uppercase tracking-tight font-medium">{{ data.role }}</span>
            </div>
        </div>
        
        <div v-if="data.team" class="mt-2 flex items-center justify-between border-t pt-2 border-dashed">
            <Badge variant="outline" class="text-[9px] px-1.5 py-0 bg-muted/50">
                {{ data.team }}
            </Badge>
        </div>

        <Handle type="source" :position="Position.Bottom" class="!bg-muted-foreground/30 !w-3 !h-3" />
    </div>
</template>

<style scoped>
.vue-flow__handle {
    border-radius: 999px;
    border: 2px solid white;
}
</style>
