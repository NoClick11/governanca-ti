<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Acesso ao Sistema" />

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-500">
            {{ status }}
        </div>

        <div class="mb-8">
            <h2 class="text-xl font-bold text-white">Bem-vindo de volta</h2>
            <p class="text-sm text-gray-500 mt-1">Entre com suas credenciais para acessar o painel.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="E-mail" class="text-gray-300" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="exemplo@email.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Senha" class="text-gray-300" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        Esqueceu a senha?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full bg-gray-950/50 border-gray-800 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" class="bg-gray-950 border-gray-800 text-indigo-600 focus:ring-indigo-500" />
                <span class="ms-2 text-sm text-gray-500">Lembrar-me</span>
            </div>

            <div class="flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center py-3 bg-gradient-to-r from-indigo-500 to-purple-600 border-none hover:from-indigo-600 hover:to-purple-700 shadow-lg shadow-indigo-500/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Acessar Conta
                </PrimaryButton>

                <p class="text-center text-sm text-gray-500">
                    Não tem uma conta?
                    <Link
                        :href="route('register')"
                        class="text-indigo-400 font-medium hover:text-indigo-300 transition-colors"
                    >
                        Crie uma agora
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
