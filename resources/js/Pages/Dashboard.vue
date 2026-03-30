<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    levelDistribution: Array,
    recentAssessments: Array,
});

const user = computed(() => usePage().props.auth.user);

const gaugeOptions = computed(() => ({
    chart: {
        type: 'radialBar',
        height: 280,
        sparkline: { enabled: false },
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 135,
            hollow: { size: '65%' },
            track: {
                background: '#1e293b',
                strokeWidth: '100%',
            },
            dataLabels: {
                name: {
                    offsetY: -10,
                    color: '#94a3b8',
                    fontSize: '13px',
                    fontWeight: '500',
                },
                value: {
                    color: '#f1f5f9',
                    fontSize: '32px',
                    fontWeight: '700',
                    show: true,
                    formatter: () => props.stats.averageScore,
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
    labels: ['Média de Pontuação'],
}));

const gaugeSeries = computed(() => {
    const pct = props.stats.maxScore > 0
        ? Math.round((props.stats.averageScore / props.stats.maxScore) * 100)
        : 0;
    return [pct];
});

const barOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 280,
        toolbar: { show: false },
        background: 'transparent',
    },
    plotOptions: {
        bar: {
            borderRadius: 8,
            columnWidth: '50%',
            distributed: true,
        },
    },
    colors: props.levelDistribution.map(l => l.color),
    dataLabels: {
        enabled: true,
        style: { colors: ['#fff'], fontWeight: 700, fontSize: '14px' },
    },
    xaxis: {
        categories: props.levelDistribution.map(l => l.name),
        labels: { style: { colors: '#94a3b8', fontSize: '12px' } },
    },
    yaxis: {
        labels: { style: { colors: '#94a3b8' } },
        forceNiceScale: true,
    },
    legend: { show: false },
    grid: { borderColor: '#1e293b', strokeDashArray: 4 },
    theme: { mode: 'dark' },
    tooltip: {
        theme: 'dark',
        y: { formatter: (val) => `${val} avaliação(ões)` },
    },
}));

const barSeries = computed(() => [{
    name: 'Avaliações',
    data: props.levelDistribution.map(l => l.count),
}]);

const statCards = computed(() => [
    {
        title: 'Total de Avaliações',
        value: props.stats.totalAssessments,
        icon: 'assignment',
        gradient: 'from-indigo-500 to-purple-600',
    },
    {
        title: 'Avaliações Concluídas',
        value: props.stats.completedAssessments,
        icon: 'task_alt',
        gradient: 'from-emerald-500 to-teal-600',
    },
    {
        title: 'Pontuação Média',
        value: `${props.stats.averageScore} / ${props.stats.maxScore}`,
        icon: 'analytics',
        gradient: 'from-amber-500 to-orange-600',
    },
    {
        title: 'Empresas',
        value: props.stats.totalCompanies,
        icon: 'business',
        gradient: 'from-cyan-500 to-blue-600',
        show: user.value?.role === 'admin_global',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-white">Dashboard</h1>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div
                v-for="card in statCards.filter(c => c.show !== false)"
                :key="card.title"
                class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-5 hover:border-gray-600/50 transition-all duration-300"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">{{ card.title }}</p>
                        <p class="text-2xl font-bold text-white">{{ card.value }}</p>
                    </div>
                    <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center', card.gradient]">
                        <span class="material-symbols-outlined text-white text-xl">{{ card.icon }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Gauge Chart -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">
                    <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">speed</span>
                    Pontuação Média
                </h3>
                <apexchart
                    type="radialBar"
                    height="280"
                    :options="gaugeOptions"
                    :series="gaugeSeries"
                />
            </div>

            <!-- Bar Chart -->
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">
                    <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">bar_chart</span>
                    Distribuição por Nível
                </h3>
                <apexchart
                    type="bar"
                    height="280"
                    :options="barOptions"
                    :series="barSeries"
                />
            </div>
        </div>

        <!-- Recent Assessments -->
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">
                    <span class="material-symbols-outlined text-indigo-400 mr-2 align-middle">history</span>
                    Avaliações Recentes
                </h3>
                <Link
                    :href="route('assessments.index')"
                    class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors"
                >
                    Ver todas →
                </Link>
            </div>

            <div v-if="recentAssessments.length === 0" class="text-center py-8 text-gray-500">
                <span class="material-symbols-outlined text-4xl mb-2 block">inbox</span>
                Nenhuma avaliação realizada ainda.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-700/50">
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Avaliador</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Pontuação</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Nível</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <tr v-for="assessment in recentAssessments" :key="assessment.id" class="hover:bg-gray-700/20 transition-colors">
                            <td class="py-3 text-sm text-gray-200">{{ assessment.company_name }}</td>
                            <td class="py-3 text-sm text-gray-400">{{ assessment.user_name }}</td>
                            <td class="py-3"><StatusBadge :status="assessment.status" size="sm" /></td>
                            <td class="py-3 text-sm text-gray-200 font-medium">{{ assessment.total_score ?? '—' }}</td>
                            <td class="py-3 text-sm text-gray-200">{{ assessment.maturity_level ?? '—' }}</td>
                            <td class="py-3 text-sm text-gray-400">{{ assessment.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
