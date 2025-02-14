<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    events: Array,
});

const statusColors = {
    upcoming: 'bg-blue-100 text-blue-800',
    ongoing: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
};

const formatCurrency = (value) => {
    return `RF ${parseFloat(value).toFixed(2)}`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Events Dashboard</h2>
                </div>
                <Link :href="route('events.create')"
                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm">
                Create Event
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-6">
                            <div v-for="event in events" :key="event.id" class="flex flex-col border rounded-lg p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-3">
                                            <h3 class="text-xl font-semibold">{{ event.name }}</h3>
                                            <span
                                                :class="[statusColors[event.status], 'px-2 py-1 rounded-full text-xs']">
                                                {{ event.status }}
                                            </span>
                                        </div>

                                        <div class="mt-4 flex space-x-4">
                                            <Link :href="route('events.dashboard', event.id)"
                                                class="text-blue-600 hover:text-blue-800">
                                            View Dashboard
                                            </Link>
                                        </div>

                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Total Sales: {{ event.total_sales }}</p>
                                    <p class="font-medium">Revenue: {{ formatCurrency(event.total_revenue) }}</p>
                                        <p class="text-sm"
                                            :class="event.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                            Net Profit: {{ formatCurrency(event.net_profit) }}
                                        </p>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
