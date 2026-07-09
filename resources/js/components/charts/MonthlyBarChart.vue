<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    data: Array<{ label: string; value: number }>;
    title?: string;
    color?: string;
    formatValue?: (value: number) => string;
}>();

const width = 900;
const height = 320;
const padding = { top: 20, right: 20, bottom: 60, left: 70 };
const chartWidth = width - padding.left - padding.right;
const chartHeight = height - padding.top - padding.bottom;

const maxValue = computed(() => Math.max(1, ...props.data.map((d) => d.value)));

// Round the axis max up to a "nice" number so gridline labels aren't jagged.
const niceMax = computed(() => {
    const raw = maxValue.value;
    const magnitude = Math.pow(10, Math.floor(Math.log10(raw || 1)));
    const step = magnitude / 2;
    return Math.ceil(raw / step) * step;
});

const gridLines = computed(() => {
    const lines = [];
    for (let i = 0; i <= 5; i++) {
        lines.push((niceMax.value / 5) * i);
    }
    return lines;
});

const barWidth = computed(() => (chartWidth / props.data.length) * 0.6);
const barGap = computed(() => (chartWidth / props.data.length) * 0.4);

const bars = computed(() =>
    props.data.map((d, i) => {
        const slot = chartWidth / props.data.length;
        const barHeight = niceMax.value > 0 ? (d.value / niceMax.value) * chartHeight : 0;
        return {
            ...d,
            x: padding.left + i * slot + barGap.value / 2,
            y: padding.top + (chartHeight - barHeight),
            width: barWidth.value,
            height: barHeight,
            labelX: padding.left + i * slot + slot / 2,
        };
    }),
);

const defaultFormat = (value: number) => {
    if (value >= 1000) return `${Math.round(value / 1000)}K`;
    return String(Math.round(value));
};
const format = computed(() => props.formatValue ?? defaultFormat);
</script>

<template>
    <div class="w-full">
        <p v-if="title" class="text-center text-sm font-semibold text-muted-foreground mb-2">
            {{ title }}
        </p>
        <svg :viewBox="`0 0 ${width} ${height}`" class="w-full h-auto" preserveAspectRatio="xMidYMid meet">
            <!-- Gridlines + y-axis labels -->
            <g v-for="(gridValue, i) in gridLines" :key="i">
                <line
                    :x1="padding.left"
                    :x2="width - padding.right"
                    :y1="padding.top + chartHeight - (gridValue / niceMax) * chartHeight"
                    :y2="padding.top + chartHeight - (gridValue / niceMax) * chartHeight"
                    class="stroke-border"
                    stroke-width="1"
                />
                <text
                    :x="padding.left - 10"
                    :y="padding.top + chartHeight - (gridValue / niceMax) * chartHeight + 4"
                    text-anchor="end"
                    class="fill-muted-foreground text-[10px]"
                >
                    {{ format(gridValue) }}
                </text>
            </g>

            <!-- Bars -->
            <g v-for="bar in bars" :key="bar.label">
                <rect
                    :x="bar.x"
                    :y="bar.y"
                    :width="bar.width"
                    :height="Math.max(bar.height, bar.value > 0 ? 2 : 0)"
                    rx="4"
                    :fill="color || '#0891b2'"
                >
                    <title>{{ bar.label }}: {{ format(bar.value) }}</title>
                </rect>
                <text
                    :x="bar.labelX"
                    :y="height - padding.bottom + 16"
                    text-anchor="end"
                    class="fill-muted-foreground text-[10px]"
                    :transform="`rotate(-45 ${bar.labelX} ${height - padding.bottom + 16})`"
                >
                    {{ bar.label }}
                </text>
            </g>
        </svg>
    </div>
</template>
