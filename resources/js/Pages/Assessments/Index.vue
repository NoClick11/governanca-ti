<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    assessments: Object,
    companies: Array,
    filters: Object,
});

const showCompanyModal = ref(false);
const selectedCompanyId = ref(null);

const startNewAssessment = () => {
    if (props.companies.length > 1) {
        showCompanyModal.value = true;
    } else {
        const companyId = props.companies[0]?.id;
        router.post(route('assessments.create'), { company_id: companyId });
    }
};

const confirmSelection = () => {
    if (!selectedCompanyId.value) return;
    
    router.post(route('assessments.create'), { 
        company_id: selectedCompanyId.value 
    });
    showCompanyModal.value = false;
};
</script>

<template>
    <Head title="Avaliações" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-white">Avaliações</h1>
        </template>

        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex gap-2">
                    <button
                        v-for="s in [
                            { value: '', label: 'Todas' },
                            { value: 'in_progress', label: 'Em Andamento' },
                            { value: 'completed', label: 'Concluídas' },
                        ]"
                        :key="s.value"
                        @click="router.get(route('assessments.index'), { status: s.value }, { preserveState: true })"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            (filters?.status || '') === s.value
                                ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30'
                                : 'text-gray-400 hover:bg-gray-700/50 border border-transparent',
                        ]"
                    >
                        {{ s.label }}
                    </button>
                </div>
                <button
                    @click="startNewAssessment"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                >
                    <span class="material-symbols-outlined text-lg">play_arrow</span>
                    Iniciar Avaliação
                </button>
            </div>

            <!-- Assessments Grid -->
            <div class="space-y-3">
                <div
                    v-for="assessment in assessments.data"
                    :key="assessment.id"
                    class="bg-gray-900/30 border border-gray-700/30 rounded-xl p-4 hover:border-gray-600/50 transition-all"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div :class="[
                                'w-12 h-12 rounded-xl flex items-center justify-center',
                                assessment.status === 'completed' ? 'bg-emerald-500/20' : 'bg-amber-500/20',
                            ]">
                                <span :class="[
                                    'material-symbols-outlined text-2xl',
                                    assessment.status === 'completed' ? 'text-emerald-400' : 'text-amber-400',
                                ]">
                                    {{ assessment.status === 'completed' ? 'task_alt' : 'pending' }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-200">{{ assessment.company_name }}</h3>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs text-gray-500">
                                        <span class="material-symbols-outlined text-xs align-middle mr-0.5">person</span>
                                        {{ assessment.user_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <span class="material-symbols-outlined text-xs align-middle mr-0.5">calendar_today</span>
                                        {{ assessment.created_at }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Progress or Score -->
                            <div v-if="assessment.status === 'in_progress'" class="text-right">
                                <p class="text-xs text-gray-500">Progresso</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-24 h-2 bg-gray-700 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full transition-all"
                                            :style="{ width: assessment.progress.percentage + '%' }"
                                        />
                                    </div>
                                    <span class="text-xs text-gray-400 font-mono">{{ assessment.progress.percentage }}%</span>
                                </div>
                            </div>
                            <div v-else class="text-right">
                                <p class="text-xs text-gray-500">Pontuação</p>
                                <p class="text-lg font-bold text-white">{{ assessment.total_score }}<span class="text-sm text-gray-500">/{{ assessment.max_score }}</span></p>
                            </div>

                            <StatusBadge :status="assessment.status" size="sm" />

                            <!-- Action -->
                            <Link
                                v-if="assessment.status === 'in_progress'"
                                :href="route('assessments.show', assessment.id)"
                                class="p-2 text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-colors"
                                title="Continuar Avaliação"
                            >
                                <span class="material-symbols-outlined">play_arrow</span>
                            </Link>
                            <Link
                                v-else
                                :href="route('assessments.report', assessment.id)"
                                class="p-2 text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition-colors"
                                title="Ver Relatório"
                            >
                                <span class="material-symbols-outlined">analytics</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="assessments.data.length === 0" class="text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-5xl mb-3 block">assessment</span>
                <p class="text-lg font-medium mb-1">Nenhuma avaliação encontrada</p>
                <p class="text-sm">Clique em "Iniciar Avaliação" para começar.</p>
            </div>

            <!-- Pagination -->
            <div v-if="assessments.last_page > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-700/50">
                <p class="text-sm text-gray-400">
                    Mostrando {{ assessments.from }} a {{ assessments.to }} de {{ assessments.total }}
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in assessments.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            link.active ? 'bg-indigo-500 text-white' : link.url ? 'text-gray-400 hover:bg-gray-700/50' : 'text-gray-600 cursor-not-allowed',
                        ]"
                        v-html="link.label"
                        preserve-state
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Company Selection Modal -->
    <Modal :show="showCompanyModal" @close="showCompanyModal = false" max-width="md">
        <div class="p-6 bg-gray-900">
            <h2 class="text-lg font-bold text-white mb-4">Selecione a Empresa</h2>
            <p class="text-sm text-gray-400 mb-6">
                Escolha qual empresa você deseja avaliar agora.
            </p>

            <div class="space-y-2 mb-6">
                <button
                    v-for="company in companies"
                    :key="company.id"
                    @click="selectedCompanyId = company.id"
                    :class="[
                        'w-full text-left px-4 py-3 rounded-xl border transition-all duration-200',
                        selectedCompanyId === company.id
                            ? 'bg-indigo-500/10 border-indigo-500 text-white ring-1 ring-indigo-500'
                            : 'bg-gray-800 border-gray-700 text-gray-300 hover:border-gray-600'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ company.name }}</span>
                        <span v-if="selectedCompanyId === company.id" class="material-symbols-outlined text-indigo-400">check_circle</span>
                    </div>
                </button>
            </div>

            <div class="flex justify-end gap-3">
                <SecondaryButton @click="showCompanyModal = false">Cancelar</SecondaryButton>
                <PrimaryButton 
                    @click="confirmSelection" 
                    :disabled="!selectedCompanyId"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 border-none"
                >
                    Iniciar Avaliação
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
