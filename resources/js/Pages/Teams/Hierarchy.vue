<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Maximize2, Minimize2, Network } from 'lucide-vue-next';
import { VueFlow, useVueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import UserNode from '@/components/Hierarchy/UserNode.vue';
import { ref, onMounted, defineComponent, h } from 'vue';
import dagre from 'dagre';

// Import Vue Flow styles
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import '@vue-flow/controls/dist/style.css';

interface User {
    id: number;
    name: string;
    email: string;
    initials: string;
    role: string;
    parent_id: number | null;
    team: string | null;
}

const props = defineProps<{
    users: User[];
}>();

const nodeTypes = {
    user: UserNode,
};

const breadcrumbs = [
    { title: 'Teams', href: route('teams.index') },
    { title: 'Hierarchy', href: route('teams.hierarchy') },
];

const { onPaneReady, fitView, addEdges, addNodes } = useVueFlow();

const layout = (users: User[]) => {
    const dagreGraph = new dagre.graphlib.Graph();
    dagreGraph.setDefaultEdgeLabel(() => ({}));
    dagreGraph.setGraph({ rankdir: 'TB', nodesep: 100, ranksep: 100 });

    const nodes = users.map((user) => {
        const node = {
            id: user.id.toString(),
            type: 'user',
            data: {
                name: user.name,
                initials: user.initials,
                role: user.role,
                team: user.team,
            },
            position: { x: 0, y: 0 },
        };
        dagreGraph.setNode(node.id, { width: 220, height: 100 });
        return node;
    });

    const edges = users
        .filter((user) => user.parent_id !== null)
        .map((user) => {
            const edge = {
                id: `e${user.parent_id}-${user.id}`,
                source: user.parent_id!.toString(),
                target: user.id.toString(),
                animated: true,
                style: { stroke: '#94a3b8', strokeWidth: 2 },
            };
            dagreGraph.setEdge(edge.source, edge.target);
            return edge;
        });

    dagre.layout(dagreGraph);

    nodes.forEach((node) => {
        const nodeWithPosition = dagreGraph.node(node.id);
        node.position = {
            x: nodeWithPosition.x - 110,
            y: nodeWithPosition.y - 50,
        };
    });

    return { nodes, edges };
};

const { nodes, edges } = layout(props.users);

onPaneReady(() => {
    fitView();
});
</script>

<template>
    <Head title="Team Hierarchy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="route('teams.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Teams
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Team Hierarchy</h1>
                        <p class="text-xs text-muted-foreground">Visualizing management relationships and team structure.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                     <Badge variant="outline" class="hidden sm:flex">
                        {{ users.length }} Members
                    </Badge>
                </div>
            </div>

            <div class="flex-1 relative bg-muted/30">
                <VueFlow
                    :nodes="nodes"
                    :edges="edges"
                    :node-types="nodeTypes"
                    :fit-view-on-init="true"
                    class="h-full w-full"
                >
                    <Background :pattern-color="'#cbd5e1'" :gap="20" />
                    <Controls />
                </VueFlow>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Custom flow styles to match project aesthetic */
.vue-flow__node-user {
    padding: 0;
    border: none;
    background: transparent;
}

.vue-flow__edge-path {
    stroke: #94a3b8;
    stroke-width: 2;
}

.vue-flow__controls {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.vue-flow__controls-button {
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 4px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s;
}

.vue-flow__controls-button:hover {
    background: #f8fafc;
    color: #0f172a;
}
</style>
