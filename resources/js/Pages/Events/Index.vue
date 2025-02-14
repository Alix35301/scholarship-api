<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useFlash } from '@/Composables/useFlash';
import {
    ChartBarIcon,
    EyeIcon,
    PencilSquareIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Object,
});

const { flash } = useFlash();

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

const deleteEvent = (id) => {
    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(route('events.destroy', id));
    }
};
</script>

<template>
    <Head title="Events" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events' }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Events</h2>
                    <Link
                        :href="route('events.create')"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm"
                    >
                        Create Event
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="flash && flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ flash.message }}
                        </div>

                        <div class="space-y-6">
                            <div v-for="event in events.data" :key="event.id" class="border rounded-lg p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-3">
                                            <h3 class="text-xl font-semibold">{{ event.name }}</h3>
                                            <span :class="[statusColors[event.status], 'px-2 py-1 rounded-full text-xs']">
                                                {{ event.status }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p>{{ event.description }}</p>
                                            <p class="mt-1">Location: {{ event.location }}</p>
                                            <p class="mt-1">
                                                {{ formatDate(event.start_date) }}
                                                <template v-if="event.end_date">
                                                    - {{ formatDate(event.end_date) }}
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium">Revenue: {{ formatCurrency(event.total_revenue) }}</p>
                                        <p class="text-sm" :class="event.total_revenue - event.total_expense >= 0 ? 'text-green-600' : 'text-red-600'">
                                            Net: {{ formatCurrency(event.total_revenue - event.total_expense) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-4">
                                    <Link
                                        :href="route('events.dashboard', event.id)"
                                        class="text-blue-600 hover:text-blue-800 flex items-center gap-1"
                                    >
                                        <ChartBarIcon class="w-4 h-4" />
                                        <span class="hidden sm:inline">Dashboard</span>
                                    </Link>
 
                                    <Link
                                        :href="route('events.edit', event.id)"
                                        class="text-yellow-600 hover:text-yellow-800 flex items-center gap-1"
                                    >
                                        <PencilSquareIcon class="w-4 h-4" />
                                        <span class="hidden sm:inline">Edit</span>
                                    </Link>
                                    <button
                                        @click="deleteEvent(event.id)"
                                        class="text-red-600 hover:text-red-800 flex items-center gap-1"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
