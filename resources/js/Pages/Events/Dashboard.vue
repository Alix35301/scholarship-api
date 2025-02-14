<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFlash } from '@/Composables/useFlash';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
    salesByStatus: Object,
    topProducts: Array,
    topSellers: Array,
    recentSales: Array,
    currentDateFilter: String,
    filteredTotals: Object,
});

const { flash } = useFlash();

const startDate = ref('');
const endDate = ref('');
const showCustomDatePicker = ref(false);

const handleDateFilterChange = (filter) => {
    startDate.value = '';
    endDate.value = '';
    showCustomDatePicker.value = filter === 'custom';

    if (filter !== 'custom') {
        router.get(route('events.dashboard', props.event.id), { date: filter }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const applyCustomDateFilter = () => {
    if (startDate.value && endDate.value) {
        router.get(route('events.dashboard', props.event.id), {
            date: 'custom',
            start_date: startDate.value,
            end_date: endDate.value
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'MVR'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusColor = (status) => {
    const colors = {
        upcoming: 'bg-blue-100 text-blue-800',
        ongoing: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`${event.name} - Dashboard`" />

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
                <div class="flex flex-col">
                    <div class="flex justify-between items-center space-x-4">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ event.name }} - Dashboard</h2>
                        <Link
                            :href="route('events.edit', event.id)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm"
                        >
                            Edit Event
                        </Link>
                    </div>
                    <div class="flex space-x-4">
                        <div class="flex items-center space-x-2">
                            <button
                                v-for="(label, filter) in {
                                    all: 'All Time',
                                    today: 'Today',
                                    custom: 'Custom Range'
                                }"
                                :key="filter"
                                @click="handleDateFilterChange(filter)"
                                :class="[
                                    'px-3 py-1 text-sm rounded-md',
                                    (filter === 'all' && !currentDateFilter) || currentDateFilter === filter
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                            >
                                {{ label }}
                            </button>
                        </div>
                        <!-- Custom Date Range Picker -->
                        <div v-if="showCustomDatePicker" class="flex items-center space-x-2">
                            <input
                                type="date"
                                v-model="startDate"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <span class="text-gray-500">to</span>
                            <input
                                type="date"
                                v-model="endDate"
                                :min="startDate"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button
                                @click="applyCustomDateFilter"
                                :disabled="!startDate || !endDate"
                                class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            >
                                Apply
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="flash && flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ flash.message }}
                </div>

                <!-- Event Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Event Status</h3>
                            <div class="space-y-3">
                                <div>
                                    <span :class="['px-3 py-1 rounded-full text-sm', getStatusColor(event.status)]">
                                        {{ event.status }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Start Date:</span>
                                    <span class="ml-2">{{ formatDate(event.start_date) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">End Date:</span>
                                    <span class="ml-2">{{ formatDate(event.end_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Financial Overview</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-600">Total Expense:</span>
                                    <span class="ml-2 font-medium">{{ formatCurrency(filteredTotals.expense) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Revenue:</span>
                                    <span class="ml-2 font-medium">{{ formatCurrency(filteredTotals.revenue) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Net Profit:</span>
                                    <span
                                        class="ml-2 font-medium"
                                        :class="filteredTotals.net_profit >= 0 ? 'text-green-600' : 'text-red-600'"
                                    >
                                        {{ formatCurrency(filteredTotals.net_profit) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Sales Overview</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-600">Total Sales:</span>
                                    <span class="ml-2 font-medium">{{ event.total_sales_count }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Products:</span>
                                    <span class="ml-2 font-medium">{{ event.total_products_count }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">FOC Value:</span>
                                    <span class="ml-2 font-medium">{{ formatCurrency(event.foc_value) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <Link
                                :href="route('events.products.index', event.id)"
                                class="flex items-center justify-center px-4 py-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100"
                            >
                                <span class="text-sm font-medium">Manage Products</span>
                            </Link>
                            <Link
                                :href="route('events.sales.create', event.id)"
                                class="flex items-center justify-center px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100"
                            >
                                <span class="text-sm font-medium">New Sale</span>
                            </Link>
                            <Link
                                :href="route('events.expenses.create', event.id)"
                                class="flex items-center justify-center px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100"
                            >
                                <span class="text-sm font-medium">Add Expense</span>
                            </Link>
                            <Link
                                :href="route('events.report', event.id)"
                                class="flex items-center justify-center px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100"
                            >
                                <span class="text-sm font-medium">Generate Report</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold">Event Details</h3>
                            <Link
                                :href="route('events.edit', event.id)"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                Edit Details
                            </Link>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <span class="text-gray-600">Description:</span>
                                    <p class="mt-1 text-gray-900">{{ event.description || 'No description provided' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Location:</span>
                                    <p class="mt-1 text-gray-900">{{ event.location }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Budget:</span>
                                    <p class="mt-1 text-gray-900">{{ formatCurrency(event.budget) }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <span class="text-gray-600">Duration:</span>
                                    <p class="mt-1 text-gray-900">
                                        {{ formatDate(event.start_date) }} - {{ formatDate(event.end_date) }}
                                        ({{ Math.ceil((new Date(event.end_date) - new Date(event.start_date)) / (1000 * 60 * 60 * 24)) }} days)
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales by Status, Top Products, and Top Sellers -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Expense Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Expense Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total Expenses</span>
                                    <span class="font-medium">{{ formatCurrency(filteredTotals.expense) }}</span>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Top Products</h3>
                            <div class="space-y-4">
                                <div v-for="product in topProducts" :key="product.id" class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium">{{ product.name }}</div>
                                        <div class="text-sm text-gray-600">
                                            Sold: {{ product.total_quantity || 0 }} units
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">{{ formatCurrency(product.total_revenue || 0) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Top Sellers</h3>
                            <div class="space-y-4">
                                <div v-for="(seller, index) in topSellers" :key="seller.seller_id" class="flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full" :class="{
                                            'bg-yellow-100 text-yellow-800': index === 0,
                                            'bg-gray-100 text-gray-800': index === 1,
                                            'bg-orange-100 text-orange-800': index === 2,
                                            'bg-blue-100 text-blue-800': index > 2
                                        }">
                                            {{ index + 1 }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ seller.seller.name }}</div>
                                            <div class="text-sm text-gray-600">
                                                {{ seller.total_sales }} sales
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">{{ formatCurrency(seller.total_revenue) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Sales</h3>
                            <Link
                                :href="route('events.sales.create', { event: event.id })"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 text-sm"
                            >
                                New Sale
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="sale in recentSales" :key="sale.id" class="border-b pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-3">
                                            <span class="font-medium">#{{ sale.id }}</span>
                                            <span :class="['px-2 py-1 rounded text-xs', getStatusColor(sale.status)]">
                                                {{ sale.status }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            by {{ sale.seller.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            {{ formatDate(sale.created_at) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium">{{ formatCurrency(sale.total_amount) }}</div>
                                        <div class="text-sm text-gray-600">
                                            {{ sale.items.length }} items
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Expenses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Expenses</h3>
                            <Link
                                :href="route('events.expenses.create', { event: event.id })"
                                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-500 text-sm"
                            >
                                Add Expense
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="expense in event.expenses" :key="expense.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ expense.title }}</div>
                                            <div class="text-sm text-gray-500">{{ expense.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium">{{ formatCurrency(expense.amount) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-blue-100 text-blue-800': expense.type === 'individual',
                                                'bg-purple-100 text-purple-800': expense.type === 'shared'
                                            }">
                                                {{ expense.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-yellow-100 text-yellow-800': expense.status === 'pending',
                                                'bg-green-100 text-green-800': expense.status === 'approved',
                                                'bg-red-100 text-red-800': expense.status === 'rejected'
                                            }">
                                                {{ expense.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(expense.date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('events.expenses.show', [event.id, expense.id])"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
