<script setup>
const props = defineProps({
    status: String,
    size: {
        type: String,
        default: 'md', // sm, md
    },
});

const statusMap = {
    in_progress: { label: 'Em Andamento', color: 'bg-amber-500/20 text-amber-400 border-amber-500/30' },
    completed: { label: 'Concluída', color: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' },
    active: { label: 'Ativo', color: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' },
    inactive: { label: 'Inativo', color: 'bg-red-500/20 text-red-400 border-red-500/30' },
    admin_global: { label: 'Admin Global', color: 'bg-purple-500/20 text-purple-400 border-purple-500/30' },
    admin_cliente: { label: 'Usuário', color: 'bg-blue-500/20 text-blue-400 border-blue-500/30' },
};

const config = statusMap[props.status] || { label: props.status, color: 'bg-gray-500/20 text-gray-400 border-gray-500/30' };
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-lg border font-medium',
            config.color,
            size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs',
        ]"
    >
        {{ config.label }}
    </span>
</template>
