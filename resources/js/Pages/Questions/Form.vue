<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    question: { type: Object, default: null },
    nextOrder: { type: Number, default: 1 },
});

const isEditing = !!props.question;

const form = useForm({
    title: props.question?.title || '',
    description: props.question?.description || '',
    order: props.question?.order || props.nextOrder,
    is_active: props.question?.is_active ?? true,
    options: props.question?.options?.length
        ? props.question.options.map(o => ({ id: o.id, text: o.text, weight: o.weight, order: o.order }))
        : [
            { id: null, text: '', weight: 1, order: 1 },
            { id: null, text: '', weight: 2, order: 2 },
            { id: null, text: '', weight: 3, order: 3 },
            { id: null, text: '', weight: 4, order: 4 },
            { id: null, text: '', weight: 5, order: 5 },
        ],
});

const addOption = () => {
    const nextOrder = form.options.length + 1;
    form.options.push({ id: null, text: '', weight: nextOrder, order: nextOrder });
};

const removeOption = (index) => {
    if (form.options.length <= 2) return;
    form.options.splice(index, 1);
    form.options.forEach((opt, i) => (opt.order = i + 1));
};

const submit = () => {
    if (isEditing) {
        form.put(route('questions.update', props.question.id));
    } else {
        form.post(route('questions.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Questão' : 'Nova Questão'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('questions.index')" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-xl font-bold text-white">{{ isEditing ? 'Editar Questão' : 'Nova Questão' }}</h1>
            </div>
        </template>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Question Info -->
                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Dados da Questão</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Título da Questão *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="Ex: Governança de TI"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-400">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Descrição</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none"
                                placeholder="Descreva o que esta questão avalia..."
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1.5">Ordem *</label>
                                <input
                                    v-model.number="form.order"
                                    type="number"
                                    min="1"
                                    class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="flex items-end">
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input v-model="form.is_active" type="checkbox" class="sr-only peer" />
                                        <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500 peer-checked:after:bg-white"></div>
                                    </label>
                                    <span class="text-sm text-gray-300">Ativa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options / Alternatives -->
                <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">
                            Alternativas ({{ form.options.length }})
                        </h2>
                        <button
                            type="button"
                            @click="addOption"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors"
                        >
                            <span class="material-symbols-outlined text-sm">add</span>
                            Adicionar Alternativa
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(option, index) in form.options"
                            :key="index"
                            class="flex items-start gap-3 bg-gray-900/30 border border-gray-700/30 rounded-xl p-3"
                        >
                            <div class="w-8 h-8 rounded-lg bg-gray-700/50 flex items-center justify-center text-gray-400 text-xs font-bold flex-shrink-0 mt-0.5">
                                {{ index + 1 }}
                            </div>
                            <div class="flex-1 space-y-2">
                                <input
                                    v-model="option.text"
                                    type="text"
                                    class="w-full px-3 py-2 bg-gray-800/50 border border-gray-700 rounded-lg text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                    :placeholder="`Texto da alternativa ${index + 1}...`"
                                />
                                <p v-if="form.errors[`options.${index}.text`]" class="text-xs text-red-400">
                                    {{ form.errors[`options.${index}.text`] }}
                                </p>
                            </div>
                            <div class="w-20 flex-shrink-0">
                                <label class="block text-xs text-gray-500 mb-1">Peso</label>
                                <input
                                    v-model.number="option.weight"
                                    type="number"
                                    min="0"
                                    class="w-full px-2 py-2 bg-gray-800/50 border border-gray-700 rounded-lg text-sm text-gray-200 text-center focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                            <button
                                v-if="form.options.length > 2"
                                type="button"
                                @click="removeOption(index)"
                                class="p-2 text-gray-500 hover:text-red-400 rounded-lg hover:bg-gray-700/50 transition-all mt-0.5"
                                title="Remover"
                            >
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>
                    </div>

                    <p v-if="form.errors.options" class="mt-2 text-sm text-red-400">{{ form.errors.options }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link :href="route('questions.index')" class="px-4 py-2.5 text-sm text-gray-400 hover:text-gray-200 transition-colors">
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                        {{ isEditing ? 'Salvar Alterações' : 'Criar Questão' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
