<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    assessment: Object,
    maturityLevel: Object,
    allLevels: Array,
    indicators: Array,
    questionScores: Array,
    maxScore: Number,
});

const scorePct = computed(() =>
    Math.round((props.assessment.total_score / props.maxScore) * 100)
);

// Gauge Chart
const gaugeOptions = computed(() => ({
    chart: {
        type: 'radialBar',
        height: 350,
        sparkline: { enabled: false },
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: {
                size: '60%',
                margin: 0,
            },
            track: {
                background: '#1e293b',
                strokeWidth: '100%',
                margin: 0,
            },
            dataLabels: {
                name: {
                    offsetY: -20,
                    color: props.maturityLevel?.color || '#94a3b8',
                    fontSize: '16px',
                    fontWeight: '700',
                },
                value: {
                    color: '#f1f5f9',
                    fontSize: '44px',
                    fontWeight: '800',
                    show: true,
                    offsetY: 5,
                    formatter: () => `${props.assessment.total_score}`,
                },
            },
        },
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            type: 'horizontal',
            colorStops: [
                { offset: 0, color: '#ef4444' },
                { offset: 33, color: '#f97316' },
                { offset: 66, color: '#22c55e' },
                { offset: 100, color: '#06b6d4' },
            ],
        },
    },
    stroke: { lineCap: 'round' },
    labels: [props.maturityLevel?.name || 'N/A'],
}));

const gaugeSeries = computed(() => [scorePct.value]);

// Bar Chart por questão
const barOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 400,
        toolbar: { show: false },
        background: 'transparent',
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 6,
            barHeight: '60%',
            distributed: true,
        },
    },
    colors: props.questionScores.map(q => {
        const pct = q.weight / q.max_weight;
        if (pct <= 0.25) return '#ef4444'; // Artesanal
        if (pct <= 0.5) return '#f97316';  // Eficiente
        if (pct <= 0.75) return '#22c55e';  // Efetiva
        return '#06b6d4';                  // Estratégica
    }),
    dataLabels: {
        enabled: true,
        formatter: (val) => `${val}/5`,
        style: { colors: ['#fff'], fontWeight: 600, fontSize: '11px' },
    },
    xaxis: {
        categories: props.questionScores.map(q =>
            q.question.length > 35 ? q.question.substring(0, 35) + '...' : q.question
        ),
        max: 5,
        labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8', fontSize: '11px' }, maxWidth: 250 },
    },
    grid: { borderColor: '#1e293b', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
    legend: { show: false },
    theme: { mode: 'dark' },
    tooltip: {
        theme: 'dark',
        custom: ({ dataPointIndex }) => {
            const q = props.questionScores[dataPointIndex];
            return `<div class="p-3">
                <p class="font-bold text-sm">${q.question}</p>
                <p class="text-xs mt-1 text-gray-400">${q.selected_option}</p>
                <p class="text-xs mt-1">Peso: <strong>${q.weight}</strong> / ${q.max_weight}</p>
            </div>`;
        },
    },
}));

const barSeries = computed(() => [{
    name: 'Pontuação',
    data: props.questionScores.map(q => q.weight),
}]);
</script>

<template>
    <Head title="Relatório de Maturidade" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('assessments.index')" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-xl font-bold text-white">Relatório de Maturidade</h1>
            </div>
        </template>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-gray-800/80 to-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ assessment.company_name }}</h2>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-400">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">person</span>
                            {{ assessment.user_name }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            {{ assessment.completed_at }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gauge Chart -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-2">
                    <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">speed</span>
                    Nota Final
                </h3>
                <p class="text-sm text-gray-400 mb-4">Pontuação de {{ assessment.total_score }} de {{ maxScore }} pontos possíveis</p>
                <apexchart
                    type="radialBar"
                    height="350"
                    :options="gaugeOptions"
                    :series="gaugeSeries"
                />
            </div>

            <!-- Maturity Level Card -->
            <div class="flex flex-col gap-6">
                <!-- Current Level -->
                <div
                    class="bg-gray-800/50 backdrop-blur-sm border-2 rounded-2xl p-6 flex-1"
                    :style="{ borderColor: maturityLevel?.color + '40' }"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center"
                            :style="{ backgroundColor: maturityLevel?.color + '20' }"
                        >
                            <span class="material-symbols-outlined text-2xl" :style="{ color: maturityLevel?.color }">
                                {{ scorePct >= 80 ? 'verified' : scorePct >= 60 ? 'trending_up' : scorePct >= 40 ? 'trending_flat' : 'warning' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Nível de Maturidade</p>
                            <h3 class="text-2xl font-bold" :style="{ color: maturityLevel?.color }">
                                {{ maturityLevel?.name || 'N/A' }}
                            </h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        {{ maturityLevel?.description }}
                    </p>
                    <div class="mt-4 pt-4 border-t border-gray-700/50">
                        <p class="text-xs text-gray-500">
                            Faixa: {{ Math.round((maturityLevel?.min_score / 100) * maxScore) }} a {{ Math.round((maturityLevel?.max_score / 100) * maxScore) }} pontos
                        </p>
                    </div>
                </div>

                <!-- Improvements Section -->
                <div v-if="maturityLevel?.improvements" class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">
                        <span class="material-symbols-outlined text-amber-400 mr-2 align-middle">lightbulb</span>
                        Sugestões de Melhoria
                    </h3>
                    <div class="space-y-2 text-sm text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ maturityLevel.improvements }}
                    </div>
                </div>

                <!-- All Levels Scale -->
                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <h4 class="text-sm font-semibold text-gray-300 mb-3">Escala de Maturidade</h4>
                    <div class="space-y-2">
                        <div
                            v-for="level in allLevels"
                            :key="level.name"
                            :class="[
                                'flex items-center justify-between px-3 py-2 rounded-lg transition-all',
                                maturityLevel?.name === level.name ? 'bg-gray-700/50 ring-1' : 'opacity-60',
                            ]"
                            :style="maturityLevel?.name === level.name ? { ringColor: level.color + '60' } : {}"
                        >
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: level.color }"></div>
                                <span class="text-sm text-gray-200 font-medium">{{ level.name }}</span>
                                <span v-if="maturityLevel?.name === level.name" class="material-symbols-outlined text-sm" :style="{ color: level.color }">arrow_back</span>
                            </div>
                            <span class="text-xs text-gray-500 font-mono">
                                {{ Math.round((level.min_score / 100) * maxScore) }}-{{ Math.round((level.max_score / 100) * maxScore) }} pts
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bar Chart: Score by Question -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">bar_chart</span>
                Pontuação por Questão
            </h3>
            <apexchart
                type="bar"
                height="400"
                :options="barOptions"
                :series="barSeries"
            />
        </div>

        <!-- Detailed Answers -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">fact_check</span>
                Detalhamento das Respostas
            </h3>
            <div class="space-y-3">
                <div
                    v-for="(q, index) in questionScores"
                    :key="index"
                    class="bg-gray-900/30 border border-gray-700/30 rounded-xl p-4"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="w-7 h-7 rounded-lg bg-gray-700/50 flex items-center justify-center text-gray-400 text-xs font-bold flex-shrink-0">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-200">{{ q.question }}</h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="material-symbols-outlined text-xs align-middle mr-0.5">check_circle</span>
                                    {{ q.selected_option }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <div class="flex gap-0.5">
                                <div
                                    v-for="i in q.max_weight"
                                    :key="i"
                                    :class="[
                                        'w-3 h-3 rounded-sm transition-all',
                                        i <= q.weight ? 'bg-indigo-500' : 'bg-gray-700',
                                    ]"
                                />
                            </div>
                            <span class="text-sm font-bold text-gray-200 ml-1">{{ q.weight }}/{{ q.max_weight }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TI Maturity Indicators -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">info</span>
                Indicadores de Nível de Maturidade da TI
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="indicator in indicators"
                    :key="indicator.name"
                    class="p-4 rounded-xl bg-gray-900/30 border border-gray-700/30"
                >
                    <span class="text-sm font-medium text-indigo-300 block mb-1">{{ indicator.name }}</span>
                    <p v-if="indicator.description" class="text-xs text-gray-500 leading-relaxed">
                        {{ indicator.description }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
