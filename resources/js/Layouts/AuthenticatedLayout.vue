<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const showSidebar = ref(true);
const showMobileMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);

const navigation = computed(() => {
    const items = [
        {
            name: 'Dashboard',
            href: route('dashboard'),
            icon: 'dashboard',
            active: route().current('dashboard'),
            show: true,
        },
        {
            name: 'Empresas',
            href: route('companies.index'),
            icon: 'business',
            active: route().current('companies.*'),
            show: ['admin_global', 'admin_cliente'].includes(user.value?.role),
        },
        {
            name: 'Usuários',
            href: route('users.index'),
            icon: 'people',
            active: route().current('users.*'),
            show: ['admin_global', 'admin_cliente'].includes(user.value?.role),
        },
        {
            name: 'Questões',
            href: route('questions.index'),
            icon: 'quiz',
            active: route().current('questions.*'),
            show: user.value?.role === 'admin_global',
        },
        {
            name: 'Avaliações',
            href: route('assessments.index'),
            icon: 'assessment',
            active: route().current('assessments.*'),
            show: true,
        },
    ];

    return items.filter(item => item.show);
});
</script>

<template>
    <div class="min-h-screen flex bg-gray-900">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 bg-gray-950 border-r border-gray-800',
                showSidebar ? 'w-64' : 'w-20',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-800">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl overflow-hidden bg-white flex items-center justify-center">
                        <img src="/images/logo-govlens.png" alt="GovLens Logo" class="w-8 h-8 object-contain">
                    </div>
                    <transition
                        enter-active-class="transition-opacity duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-100"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="showSidebar" class="text-white font-bold text-lg whitespace-nowrap">
                            GovLens
                        </span>
                    </transition>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',
                        item.active
                            ? 'bg-indigo-500/20 text-indigo-400 shadow-lg shadow-indigo-500/10'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200',
                    ]"
                >
                    <span class="material-symbols-outlined text-xl">{{ item.icon }}</span>
                    <transition
                        enter-active-class="transition-opacity duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-100"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="showSidebar">{{ item.name }}</span>
                    </transition>
                </Link>
            </nav>

            <!-- User section -->
            <div class="border-t border-gray-800 p-3">
                <Dropdown align="left" side="top" width="48" contentClasses="py-1 bg-gray-900 border border-gray-800 shadow-2xl rounded-xl overflow-hidden">
                    <template #trigger>
                        <button class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-gray-400 hover:bg-gray-800 hover:text-gray-200 transition-all duration-200">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-xs">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div v-if="showSidebar" class="flex-1 text-left min-w-0">
                                <p class="text-gray-200 font-medium truncate">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ user?.role_name }}</p>
                            </div>
                        </button>
                    </template>
                    <template #content>
                        <div class="px-4 py-3 border-b border-gray-800 bg-gray-900/50">
                            <p class="text-sm font-bold text-white">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                            <div class="mt-2">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase tracking-wider border border-indigo-500/20">
                                    {{ user?.role_name }}
                                </span>
                            </div>
                        </div>
                        <div class="p-1">
                            <DropdownLink :href="route('profile.edit')" class="rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">person</span>
                                    Meu Perfil
                                </div>
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left rounded-lg text-red-400 hover:bg-red-500/10 transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">logout</span>
                                    Sair
                                </div>
                            </DropdownLink>
                        </div>
                    </template>
                </Dropdown>
            </div>

            <!-- Toggle button -->
            <button
                @click="showSidebar = !showSidebar"
                class="absolute -right-3 top-20 w-6 h-6 rounded-full bg-gray-800 border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-700 flex items-center justify-center transition-colors z-50"
            >
                <span class="material-symbols-outlined text-sm" style="font-size: 14px;">
                    {{ showSidebar ? 'chevron_left' : 'chevron_right' }}
                </span>
            </button>
        </aside>

        <!-- Main Content -->
        <div
            :class="[
                'flex-1 flex flex-col transition-all duration-300',
                showSidebar ? 'ml-64' : 'ml-20',
            ]"
        >
            <!-- Top Bar -->
            <header class="sticky top-0 z-40 h-16 border-b border-gray-800 bg-gray-900/80 backdrop-blur-xl flex items-center justify-between px-6">
                <div>
                    <slot name="header" />
                </div>
                <div class="flex items-center gap-3">
                    <div v-if="user?.company_name" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-800 text-xs text-gray-400">
                        <span class="material-symbols-outlined text-sm" style="font-size: 16px;">business</span>
                        {{ user.company_name }}
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <FlashMessage />

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
