<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    editUser: { type: Object, default: null },
    companies: Array,
    roles: Array,
});

const currentUser = computed(() => usePage().props.auth.user);
const isEditing = !!props.editUser;

const form = useForm({
    name: props.editUser?.name || '',
    email: props.editUser?.email || '',
    password: '',
    password_confirmation: '',
    role: props.editUser?.role || 'admin_cliente',
    company_id: props.editUser?.company_id || '',
});

const submit = () => {
    if (isEditing) {
        form.put(route('users.update', props.editUser.id));
    } else {
        form.post(route('users.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Usuário' : 'Novo Usuário'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('users.index')" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-xl font-bold text-white">{{ isEditing ? 'Editar Usuário' : 'Novo Usuário' }}</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nome -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Nome *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Nome completo"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">E-mail *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="usuario@email.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <!-- Senha -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                Senha {{ isEditing ? '(deixe em branco para manter)' : '*' }}
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-400">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Confirmar Senha</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="••••••••"
                            />
                        </div>
                    </div>

                    <!-- Perfil + Empresa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Perfil *</label>
                            <select
                                v-model="form.role"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-sm text-red-400">{{ form.errors.role }}</p>
                        </div>
                        <div v-if="companies.length > 0">
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Empresa</label>
                            <select
                                v-model="form.company_id"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">Nenhuma (Admin Global)</option>
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.company_id" class="mt-1 text-sm text-red-400">{{ form.errors.company_id }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <Link :href="route('users.index')" class="px-4 py-2.5 text-sm text-gray-400 hover:text-gray-200 transition-colors">
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                            {{ isEditing ? 'Salvar Alterações' : 'Criar Usuário' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
