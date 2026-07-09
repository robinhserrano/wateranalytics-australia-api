<script setup lang="ts">
import { Bar } from 'vue-chartjs';
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    LinearScale,
    Tooltip,
    type ChartData,
    type ChartOptions,
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps<{
    monthly: Array<{ month: string; label: string; value: number; active_reps?: number }>;
    title?: string;
    color?: string;
    formatValue?: (value: number) => string;
}>();

const emit = defineEmits<{ 'bar-click': [month: string] }>();

const defaultFormat = (value: number) => {
    if (Math.abs(value) >= 1000) return `${Math.round(value / 1000)}K`;
    return String(Math.round(value));
};
const format = computed(() => props.formatValue ?? defaultFormat);

const chartData = computed<ChartData<'bar'>>(() => ({
    labels: props.monthly.map((m) => m.label),
    datasets: [
        {
            data: props.monthly.map((m) => m.value),
            backgroundColor: props.color || '#0891b2',
            borderRadius: 4,
            maxBarThickness: 40,
        },
    ],
}));

const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    onClick: (_event, elements) => {
        if (elements.length > 0) {
            const month = props.monthly[elements[0].index]?.month;
            if (month) emit('bar-click', month);
        }
    },
    onHover: (event, elements) => {
        const target = event.native?.target as HTMLElement | undefined;
        if (target) target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
    },
    plugins: {
        tooltip: {
            callbacks: {
                afterLabel: (ctx) => {
                    const reps = props.monthly[ctx.dataIndex]?.active_reps;
                    return reps !== undefined ? `${reps} active rep${reps === 1 ? '' : 's'}` : '';
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { callback: (value) => format.value(Number(value)) },
        },
    },
}));
</script>

<template>
    <div class="w-full">
        <p v-if="title" class="text-center text-sm font-semibold text-muted-foreground mb-2">
            {{ title }}
        </p>
        <div class="h-80">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
