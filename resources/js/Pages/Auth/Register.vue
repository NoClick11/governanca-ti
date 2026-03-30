<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company_name: '',
    cnpj: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
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
    <GuestLayout>
        <Head title="Criar Conta" />

        <div class="mb-8">
            <h2 class="text-xl font-bold text-white">Crie sua conta</h2>
            <p class="text-sm text-gray-500 mt-1">Inicie sua avaliação de maturidade hoje mesmo.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Dados do Usuário -->
            <div class="space-y-4">
                <h3 class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">Dados do Usuário</h3>
                
                <div>
                    <InputLabel for="name" value="Nome Completo" class="text-gray-400" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Ex: João da Silva"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="E-mail" class="text-gray-400" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="contato@exemplo.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="password" value="Senha" class="text-gray-400" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar" class="text-gray-400" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>
                </div>
            </div>

            <!-- Dados da Empresa -->
            <div class="space-y-4 pt-4 border-t border-gray-800/50">
                <h3 class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">Dados da Empresa</h3>
                
                <div>
                    <InputLabel for="company_name" value="Nome da Empresa" class="text-gray-400" />
                    <TextInput
                        id="company_name"
                        type="text"
                        class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                        v-model="form.company_name"
                        required
                        placeholder="Ex: Minha Empresa LTDA"
                    />
                    <InputError class="mt-2" :message="form.errors.company_name" />
                </div>

                <div>
                    <InputLabel for="cnpj" value="CNPJ" class="text-gray-400" />
                    <TextInput
                        id="cnpj"
                        type="text"
                        @input="formatCnpj"
                        class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-mono"
                        v-model="form.cnpj"
                        required
                        placeholder="00.000.000/0000-00"
                        maxlength="18"
                    />
                    <InputError class="mt-2" :message="form.errors.cnpj" />
                </div>
            </div>

            <div class="flex flex-col gap-4 pt-4">
                <PrimaryButton
                    class="w-full justify-center py-3 bg-gradient-to-r from-indigo-500 to-purple-600 border-none hover:from-indigo-600 hover:to-purple-700 shadow-lg shadow-indigo-500/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrar-se agora
                </PrimaryButton>

                <p class="text-center text-sm text-gray-500">
                    Já tem uma conta?
                    <Link
                        :href="route('login')"
                        class="text-indigo-400 font-medium hover:text-indigo-300 transition-colors"
                    >
                        Faça login
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
