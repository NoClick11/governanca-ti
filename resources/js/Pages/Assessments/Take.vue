<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    assessment: Object,
    questions: Array,
    progress: Object,
});

// Estado local reativo cópia profunda para permitir atualização instantânea sem lag
const localQuestions = ref(JSON.parse(JSON.stringify(props.questions)));
const currentQuestionIndex = ref(0);
const saving = ref(false);
const pendingChanges = ref(false);

const currentQuestion = computed(() => localQuestions.value[currentQuestionIndex.value]);
const isFirst = computed(() => currentQuestionIndex.value === 0);
const isLast = computed(() => currentQuestionIndex.value === localQuestions.value.length - 1);

const allAnswered = computed(() => localQuestions.value.every(q => q.selected_option_id));
const answeredCount = computed(() => localQuestions.value.filter(q => q.selected_option_id).length);
const progressPct = computed(() => Math.round((answeredCount.value / localQuestions.value.length) * 100));

// Sincroniza se as questões mudarem de fora
watch(() => props.questions, (newVal) => {
    localQuestions.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

const saveAllAnswers = async () => {
    if (!pendingChanges.value) return;
    
    saving.value = true;
    const batch = localQuestions.value
        .filter(q => q.selected_option_id)
        .map(q => ({
            question_id: q.id,
            option_id: q.selected_option_id
        }));

    router.post(
        route('assessments.answer', props.assessment.id),
        { answers: batch },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                pendingChanges.value = false;
            },
            onFinish: () => {
                saving.value = false;
            }
        }
    );
};

const selectOption = (questionId, optionId) => {
    const q = localQuestions.value.find(q => q.id === questionId);
    if (q) {
        q.selected_option_id = optionId;
        pendingChanges.value = true;
        
        // Salva de forma otimista em segundo plano
        saveAllAnswers();
    }
};

const goNext = () => {
    if (!isLast.value) {
        saveAllAnswers();
        currentQuestionIndex.value++;
    }
};

const goPrev = () => {
    if (!isFirst.value) {
        saveAllAnswers();
        currentQuestionIndex.value--;
    }
};

const goToQuestion = (index) => {
    saveAllAnswers();
    currentQuestionIndex.value = index;
};

const completeAssessment = () => {
    if (!allAnswered.value) return;
    if (confirm('Tem certeza que deseja finalizar a avaliação? Esta ação não pode ser desfeita.')) {
        const batch = localQuestions.value
            .filter(q => q.selected_option_id)
            .map(q => ({
                question_id: q.id,
                option_id: q.selected_option_id
            }));

        saving.value = true;
        router.post(
            route('assessments.answer', props.assessment.id),
            { answers: batch },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    pendingChanges.value = false;
                    router.post(route('assessments.complete', props.assessment.id));
                },
                onFinish: () => {
                    saving.value = false;
                }
            }
        );
    }
};
</script>

<template>
    <Head title="Avaliação em Andamento" />

    <div class="min-h-screen bg-gray-900 flex flex-col">
        <!-- Top Bar -->
        <header class="bg-gray-950 border-b border-gray-800 px-6 py-4">
            <div class="max-w-4xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white">quiz</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Avaliação de Maturidade</h1>
                        <div class="flex items-center gap-2 mt-0.5">
                            <p class="text-xs text-gray-500">Responda todas as questões para gerar o relatório</p>
                            <span class="w-1 h-1 rounded-full bg-gray-700"></span>
                            <span class="text-xs font-semibold flex items-center gap-1" :class="saving ? 'text-indigo-400' : 'text-emerald-400'">
                                <span class="material-symbols-outlined text-xs animate-spin" v-if="saving" style="font-size: 12px; line-height: 1;">sync</span>
                                <span class="material-symbols-outlined text-xs" v-else style="font-size: 12px; line-height: 1;">cloud_done</span>
                                {{ saving ? 'Salvando...' : 'Progresso Salvo' }}
                            </span>
                        </div>
                    </div>
                </div>
                <button
                    @click="router.get(route('assessments.index'))"
                    class="text-gray-400 hover:text-white transition-colors"
                >
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </header>

        <!-- Progress Bar -->
        <div class="bg-gray-950 px-6 pb-4">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-400">Progresso</span>
                    <span class="text-xs text-gray-400 font-mono">{{ answeredCount }}/{{ localQuestions.length }} ({{ progressPct }}%)</span>
                </div>
                <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                        :style="{ width: progressPct + '%' }"
                    />
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 px-6 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-12 gap-6">
                    <!-- Question Navigator (sidebar) -->
                    <div class="col-span-3 hidden lg:block">
                        <div class="sticky top-24 bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Questões</h3>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="(q, index) in localQuestions"
                                    :key="q.id"
                                    @click="goToQuestion(index)"
                                    :class="[
                                        'w-full aspect-square rounded-lg text-xs font-bold flex items-center justify-center transition-all',
                                        currentQuestionIndex === index
                                            ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 scale-110'
                                            : q.selected_option_id
                                                ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                                : 'bg-gray-700/50 text-gray-400 hover:bg-gray-700',
                                    ]"
                                >
                                    {{ index + 1 }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Question Content -->
                    <div class="col-span-12 lg:col-span-9">
                        <transition name="fade" mode="out-in">
                            <div :key="currentQuestion.id" class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8">
                                <!-- Question Header -->
                                <div class="mb-8">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 text-sm font-bold">
                                            {{ currentQuestionIndex + 1 }}
                                        </span>
                                        <span class="text-xs text-gray-500 uppercase tracking-wider">
                                            Questão {{ currentQuestionIndex + 1 }} de {{ localQuestions.length }}
                                        </span>
                                    </div>
                                    <h2 class="text-xl font-bold text-white">{{ currentQuestion.title }}</h2>
                                    <p v-if="currentQuestion.description" class="text-sm text-gray-400 mt-2">
                                        {{ currentQuestion.description }}
                                    </p>
                                </div>

                                <!-- Options -->
                                <div class="space-y-3">
                                    <button
                                        v-for="option in currentQuestion.options"
                                        :key="option.id"
                                        @click="selectOption(currentQuestion.id, option.id)"
                                        :disabled="saving"
                                        :class="[
                                            'w-full text-left p-4 rounded-xl border-2 transition-all duration-200',
                                            currentQuestion.selected_option_id === option.id
                                                ? 'border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10'
                                                : 'border-gray-700/50 bg-gray-900/30 hover:border-gray-600 hover:bg-gray-800/50',
                                        ]"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div :class="[
                                                'w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all',
                                                currentQuestion.selected_option_id === option.id
                                                    ? 'border-indigo-500 bg-indigo-500'
                                                    : 'border-gray-600',
                                            ]">
                                                <span v-if="currentQuestion.selected_option_id === option.id" class="material-symbols-outlined text-white text-sm" style="font-size: 14px;">check</span>
                                            </div>
                                            <span :class="[
                                                'text-sm',
                                                currentQuestion.selected_option_id === option.id
                                                    ? 'text-white font-medium'
                                                    : 'text-gray-300',
                                            ]">
                                                {{ option.text }}
                                            </span>
                                        </div>
                                    </button>
                                </div>

                                <!-- Navigation -->
                                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-700/50">
                                    <button
                                        @click="goPrev"
                                        :disabled="isFirst"
                                        :class="[
                                            'inline-flex items-center gap-2 px-4 py-2.5 text-sm rounded-xl transition-all',
                                            isFirst
                                                ? 'text-gray-600 cursor-not-allowed'
                                                : 'text-gray-300 hover:bg-gray-700/50',
                                        ]"
                                    >
                                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                                        Anterior
                                    </button>

                                    <div class="flex gap-2">
                                        <button
                                            v-if="!isLast"
                                            @click="goNext"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-500 text-white text-sm font-medium rounded-xl hover:bg-indigo-600 transition-all shadow-lg shadow-indigo-500/25"
                                        >
                                            Próxima
                                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                                        </button>
                                        <button
                                            v-if="isLast || allAnswered"
                                            @click="completeAssessment"
                                            :disabled="!allAnswered"
                                            :class="[
                                                'inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-xl transition-all',
                                                allAnswered
                                                    ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700'
                                                    : 'bg-gray-700 text-gray-500 cursor-not-allowed',
                                            ]"
                                        >
                                            <span class="material-symbols-outlined text-lg">check_circle</span>
                                            Finalizar Avaliação
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateX(10px);
}
.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
</style>
