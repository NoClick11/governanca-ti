<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success');

const flash = computed(() => page.props.flash);

watch(
    () => [flash.value?.success, flash.value?.error],
    ([success, error]) => {
        if (success) {
            message.value = success;
            type.value = 'success';
            show.value = true;
            setTimeout(() => (show.value = false), 4000);
        }
        if (error) {
            message.value = error;
            type.value = 'error';
            show.value = true;
            setTimeout(() => (show.value = false), 5000);
        }
    },
    { immediate: true }
);
</script>

<template>
    <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-[-100%] opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-[-100%] opacity-0"
    >
        <div
            v-if="show"
            :class="[
                'fixed top-4 right-4 z-[100] max-w-sm px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3',
                type === 'success'
                    ? 'bg-emerald-500/90 text-white backdrop-blur-lg'
                    : 'bg-red-500/90 text-white backdrop-blur-lg',
            ]"
        >
            <span class="material-symbols-outlined text-xl">
                {{ type === 'success' ? 'check_circle' : 'error' }}
            </span>
            <p class="text-sm font-medium">{{ message }}</p>
            <button @click="show = false" class="ml-2 hover:opacity-70 transition-opacity">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </transition>
</template>
