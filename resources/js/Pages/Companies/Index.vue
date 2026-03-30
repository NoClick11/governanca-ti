<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    companies: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('companies.index'), { search: val }, { preserveState: true, replace: true });
    }, 300);
});

const confirmDelete = (company) => {
    if (confirm(`Tem certeza que deseja excluir a empresa "${company.name}"?`)) {
        router.delete(route('companies.destroy', company.id));
    }
};
</script>

<template>
    <Head title="Empresas" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-white">Empresas</h1>
        </template>

        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
            <!-- Toolbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xl">search</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nome ou CNPJ..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                    />
                </div>
                <Link
                    :href="route('companies.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                >
                    <span class="material-symbols-outlined text-lg">add</span>
                    Nova Empresa
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left border-b border-gray-700/50">
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Nome</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">CNPJ</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">E-mail</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Telefone</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="pb-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/30">
                        <tr v-for="company in companies.data" :key="company.id" class="hover:bg-gray-700/20 transition-colors">
                            <td class="py-3.5 text-sm text-gray-200 font-medium">{{ company.name }}</td>
                            <td class="py-3.5 text-sm text-gray-400 font-mono">{{ company.cnpj }}</td>
                            <td class="py-3.5 text-sm text-gray-400">{{ company.contact_email || '—' }}</td>
                            <td class="py-3.5 text-sm text-gray-400">{{ company.contact_phone || '—' }}</td>
                            <td class="py-3.5">
                                <StatusBadge :status="company.is_active ? 'active' : 'inactive'" size="sm" />
                            </td>
                            <td class="py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('companies.edit', company.id)"
                                        class="p-2 text-gray-400 hover:text-indigo-400 hover:bg-gray-700/50 rounded-lg transition-all"
                                        title="Editar"
                                    >
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </Link>
                                    <button
                                        @click="confirmDelete(company)"
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

            <!-- Empty State -->
            <div v-if="companies.data.length === 0" class="text-center py-12 text-gray-500">
                <span class="material-symbols-outlined text-5xl mb-3 block">business</span>
                <p class="text-lg font-medium mb-1">Nenhuma empresa cadastrada</p>
                <p class="text-sm">Clique em "Nova Empresa" para começar.</p>
            </div>

            <!-- Pagination -->
            <div v-if="companies.last_page > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-700/50">
                <p class="text-sm text-gray-400">
                    Mostrando {{ companies.from }} a {{ companies.to }} de {{ companies.total }} empresas
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in companies.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            link.active
                                ? 'bg-indigo-500 text-white'
                                : link.url
                                    ? 'text-gray-400 hover:bg-gray-700/50'
                                    : 'text-gray-600 cursor-not-allowed',
                        ]"
                        v-html="link.label"
                        preserve-state
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
