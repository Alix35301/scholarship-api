<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    event: {
        type: Object,
        required: true,
    }
});

const { flash } = useFlash();

const statusColors = {
    upcoming: 'bg-blue-100 text-blue-800',
    ongoing: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
};

const getStatusClass = (status) => {
    return statusColors[status] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (value) => {
    return `RF ${parseFloat(value).toFixed(2)}`;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusColor = (status) => {
    const colors = {
        upcoming: 'bg-blue-100 text-blue-800',
        ongoing: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events', url: route('events.index') },
                        { label: event.name, url: route('events.dashboard', event.id) }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ event.name }}</h2>
                    <Link
                        :href="route('events.edit', event.id)"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm"
                    >
                        Edit Event
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="flash && flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ flash.message }}
                </div>

                <!-- Event Details Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Event Details</h3>
                                <div class="space-y-2">
                                    <p><span class="font-medium">Name:</span> {{ event.name }}</p>
                                    <p><span class="font-medium">Description:</span> {{ event.description }}</p>
                                    <p><span class="font-medium">Location:</span> {{ event.location }}</p>
                                    <p><span class="font-medium">Start Date:</span> {{ formatDate(event.start_date) }}</p>
                                    <p v-if="event.end_date"><span class="font-medium">End Date:</span> {{ formatDate(event.end_date) }}</p>
                                    <p><span class="font-medium">Budget:</span> {{ formatCurrency(event.budget) }}</p>
                                    <p><span class="font-medium">Status:</span>
                                        <span :class="getStatusClass(event.status)">{{ event.status }}</span>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Financial Overview</h3>
                                <div class="space-y-3">
                                    <div>
                                        <span class="font-medium">Total Revenue:</span>
                                        <span class="ml-2">{{ formatCurrency(event.total_revenue) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Total Expenses:</span>
                                        <span class="ml-2">{{ formatCurrency(event.total_expense) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Net Profit:</span>
                                        <span class="ml-2" :class="event.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ formatCurrency(event.net_profit) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products and Sales Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Products List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Products</h3>
                                <Link
                                    :href="route('events.products.create', event.id)"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm"
                                >
                                    Add Product
                                </Link>
                            </div>
                            <div class="space-y-4">
                                <div v-for="product in event.products" :key="product.id" class="border-b pb-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium">{{ product.name }}</div>
                                            <div class="text-sm text-gray-500">Stock: {{ product.stock }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium">{{ formatCurrency(product.price) }}</div>
                                            <div class="text-sm" :class="product.status === 'active' ? 'text-green-600' : 'text-red-600'">
                                                {{ product.status }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="event.products.length === 0" class="text-gray-500 text-center py-4">
                                    No products added yet
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Recent Sales</h3>
                                <Link
                                    :href="route('events.sales.create', event.id)"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 text-sm"
                                >
                                    New Sale
                                </Link>
                            </div>
                            <div class="space-y-4">
                                <div v-for="sale in event.sales" :key="sale.id" class="border-b pb-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium">#{{ sale.id }}</div>
                                            <div class="text-sm text-gray-500">{{ formatDate(sale.created_at) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium">{{ formatCurrency(sale.total_amount) }}</div>
                                            <span :class="['px-2 py-1 rounded text-xs', getStatusColor(sale.status)]">
                                                {{ sale.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="event.sales.length === 0" class="text-gray-500 text-center py-4">
                                    No sales recorded yet
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
