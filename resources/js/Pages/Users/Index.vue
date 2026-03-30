<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    companies: Array,
    filters: Object,
    roles: Array,
});

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');

let searchTimeout;
const applyFilters = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('users.index'), {
            search: search.value,
            role: roleFilter.value,
        }, { preserveState: true, replace: true });
    }, 300);
};

watch(search, applyFilters);
watch(roleFilter, applyFilters);

const confirmDelete = (user) => {
    if (confirm(`Tem certeza que deseja excluir o usuário "${user.name}"?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Usuários" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-white">Usuários</h1>
        </template>

        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">search</span>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nome ou e-mail..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                        />
                    </div>
                    <select
                        v-model="roleFilter"
                        class="px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">Todos os Perfis</option>
                        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                >
                    <span class="material-symbols-outlined text-lg">person_add</span>
                    Novo Usuário
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-700/50">
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Nome</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">E-mail</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Perfil</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-700/20 transition-colors">
                            <td class="py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ u.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-gray-200 font-medium">{{ u.name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-sm text-gray-400">{{ u.email }}</td>
                            <td class="py-3.5"><StatusBadge :status="u.role" size="sm" /></td>
                            <td class="py-3.5 text-sm text-gray-400">{{ u.company?.name || '—' }}</td>
                            <td class="py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('users.edit', u.id)"
                                        class="p-2 text-gray-400 hover:text-indigo-400 hover:bg-gray-700/50 rounded-lg transition-all"
                                        title="Editar"
                                    >
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </Link>
                                    <button
                                        @click="confirmDelete(u)"
                                        class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700/50 rounded-lg transition-all"
                                        title="Excluir"
                                    >
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty -->
            <div v-if="users.data.length === 0" class="text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-5xl mb-3 block">people</span>
                <p class="text-lg font-medium mb-1">Nenhum usuário encontrado</p>
            </div>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-700/50">
                <p class="text-sm text-gray-400">
                    Mostrando {{ users.from }} a {{ users.to }} de {{ users.total }}
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in users.links"
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
