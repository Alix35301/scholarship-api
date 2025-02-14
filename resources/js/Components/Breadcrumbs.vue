<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
        // Each item should have { label: string, url?: string }
    }
});
</script>

<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li v-for="(item, index) in items" :key="index" class="inline-flex items-center">
                <div v-if="index > 0" class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                </div>
                <template v-if="item.url && index !== items.length - 1">
                    <Link :href="item.url" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                        {{ item.label }}
                    </Link>
                </template>
                <template v-else>
                    <span class="text-sm font-medium text-gray-500">{{ item.label }}</span>
                </template>
            </li>
        </ol>
    </nav>
</template>
