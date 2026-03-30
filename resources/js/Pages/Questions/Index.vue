<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    questions: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('questions.index'), { search: val }, { preserveState: true, replace: true });
    }, 300);
});

const confirmDelete = (question) => {
    if (confirm(`Tem certeza que deseja excluir a questão "${question.title}"?`)) {
        router.delete(route('questions.destroy', question.id));
    }
};
</script>

<template>
    <Head title="Questões" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-white">Gestão de Questões</h1>
        </template>

        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">search</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar questões..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                    />
                </div>
                <Link
                    :href="route('questions.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                >
                    <span class="material-symbols-outlined text-lg">add</span>
                    Nova Questão
                </Link>
            </div>

            <!-- Questions List -->
            <div class="space-y-3">
                <div
                    v-for="question in questions.data"
                    :key="question.id"
                    class="bg-gray-900/30 border border-gray-700/30 rounded-xl p-4 hover:border-gray-600/50 transition-all"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm font-bold flex-shrink-0 mt-0.5">
                                {{ question.order }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-200">{{ question.title }}</h3>
                                <p v-if="question.description" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ question.description }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <span class="material-symbols-outlined text-sm align-middle mr-1">list</span>
                                        {{ question.options_count }} alternativas
                                    </span>
                                    <StatusBadge :status="question.is_active ? 'active' : 'inactive'" size="sm" />
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 ml-4">
                            <Link
                                :href="route('questions.edit', question.id)"
                                class="p-2 text-gray-400 hover:text-indigo-400 hover:bg-gray-700/50 rounded-lg transition-all"
                                title="Editar"
                            >
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </Link>
                            <button
                                @click="confirmDelete(question)"
                                class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700/50 rounded-lg transition-all"
                                title="Excluir"
                            >
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-if="questions.data.length === 0" class="text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-5xl mb-3 block">quiz</span>
                <p class="text-lg font-medium mb-1">Nenhuma questão cadastrada</p>
                <p class="text-sm">Clique em "Nova Questão" para começar.</p>
            </div>

            <!-- Pagination -->
            <div v-if="questions.last_page > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-700/50">
                <p class="text-sm text-gray-400">
                    Mostrando {{ questions.from }} a {{ questions.to }} de {{ questions.total }}
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in questions.links"
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
</template>
