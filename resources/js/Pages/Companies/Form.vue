<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: {
        type: Object,
        default: null,
    },
});

const isEditing = !!props.company;

const form = useForm({
    name: props.company?.name || '',
    cnpj: props.company?.cnpj || '',
    contact_email: props.company?.contact_email || '',
    contact_phone: props.company?.contact_phone || '',
    address: props.company?.address || '',
    is_active: props.company?.is_active ?? true,
});

const submit = () => {
    if (isEditing) {
        form.put(route('companies.update', props.company.id));
    } else {
        form.post(route('companies.store'));
    }
};

const formatCnpj = (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (val.length > 14) val = val.slice(0, 14);
    if (val.length > 12) val = val.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
    else if (val.length > 8) val = val.replace(/^(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
    else if (val.length > 5) val = val.replace(/^(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
    else if (val.length > 2) val = val.replace(/^(\d{2})(\d{1,3})/, '$1.$2');
    form.cnpj = val;
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Empresa' : 'Nova Empresa'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('companies.index')" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <h1 class="text-xl font-bold text-white">{{ isEditing ? 'Editar Empresa' : 'Nova Empresa' }}</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nome -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Nome da Empresa *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="Ex: Tech Solutions Ltda"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
                    </div>

                    <!-- CNPJ -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">CNPJ *</label>
                        <input
                            :value="form.cnpj"
                            @input="formatCnpj"
                            type="text"
                            maxlength="18"
                            class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono"
                            placeholder="00.000.000/0000-00"
                        />
                        <p v-if="form.errors.cnpj" class="mt-1 text-sm text-red-400">{{ form.errors.cnpj }}</p>
                    </div>

                    <!-- Email + Phone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">E-mail de Contato</label>
                            <input
                                v-model="form.contact_email"
                                type="email"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="contato@empresa.com"
                            />
                            <p v-if="form.errors.contact_email" class="mt-1 text-sm text-red-400">{{ form.errors.contact_email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Telefone</label>
                            <input
                                v-model="form.contact_phone"
                                type="text"
                                class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                placeholder="(00) 00000-0000"
                            />
                            <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-400">{{ form.errors.contact_phone }}</p>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Endereço</label>
                        <textarea
                            v-model="form.address"
                            rows="3"
                            class="w-full px-4 py-2.5 bg-gray-900/50 border border-gray-700 rounded-xl text-sm text-gray-200 placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none"
                            placeholder="Rua, número, bairro, cidade - UF"
                        />
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-400">{{ form.errors.address }}</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-700 peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500 peer-checked:after:bg-white"></div>
                        </label>
                        <span class="text-sm text-gray-300">Empresa Ativa</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700/50">
                        <Link
                            :href="route('companies.index')"
                            class="px-4 py-2.5 text-sm text-gray-400 hover:text-gray-200 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg">progress_activity</span>
                            {{ isEditing ? 'Salvar Alterações' : 'Cadastrar Empresa' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
