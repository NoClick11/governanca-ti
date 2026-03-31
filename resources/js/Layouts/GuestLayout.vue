<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-950 p-6 selection:bg-indigo-500/30">
        <!-- Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-500/10 blur-[120px] rounded-full"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-purple-500/10 blur-[120px] rounded-full"></div>
        </div>

        <!-- Logo/Header Area -->
        <div class="relative z-10 mb-8 flex flex-col items-center">
            <Link href="/" class="flex flex-col items-center gap-3 group">
                <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center shadow-2xl shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300 overflow-hidden">
                    <img src="/images/logo-govlens.png" alt="GovLens Logo" class="w-12 h-12 object-contain">
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">GovLens</h1>
            </Link>
        </div>

        <!-- Form Container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-gray-900/50 backdrop-blur-2xl border border-gray-800 rounded-3xl p-8 shadow-2xl ring-1 ring-white/5">
                <slot />
            </div>
            
            <!-- Footer Text -->
            <p class="mt-8 text-center text-sm text-gray-500">
                &copy; {{ new Date().getFullYear() }} Sistema de Maturidade de TI. Todos os direitos reservados.
            </p>
        </div>
    </div>
</template>
