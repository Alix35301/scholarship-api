<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    event: Object,
    salesSummary: Object,
    expenseSummary: Object,
    sellerPerformance: Array,
    productPerformance: Array,
    focAnalysis: Array,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'MVR'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`${event.name} - Report`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events', url: route('events.index') },
                        { label: event.name, url: route('events.dashboard', event.id) },
                        { label: 'Event Report' }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ event.name }} - Report</h2>
                    <div class="flex space-x-4">
                        <Link
                            :href="route('events.dashboard', event.id)"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm"
                        >
                            Back to Dashboard
                        </Link>
                        <button
                            @click="window.print()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm"
                        >
                            Print Report
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Event Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Event Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-600">Event Details</h4>
                                <div class="mt-2 space-y-2">
                                    <div>
                                        <span class="text-gray-600">Start Date:</span>
                                        <span class="ml-2">{{ formatDate(event.start_date) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">End Date:</span>
                                        <span class="ml-2">{{ formatDate(event.end_date) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Location:</span>
                                        <span class="ml-2">{{ event.location }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-600">Financial Summary</h4>
                                <div class="mt-2 space-y-2">
                                    <div>
                                        <span class="text-gray-600">Budget:</span>
                                        <span class="ml-2">{{ formatCurrency(event.budget) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Revenue:</span>
                                        <span class="ml-2">{{ formatCurrency(salesSummary.total_revenue) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Net Profit:</span>
                                        <span
                                            class="ml-2"
                                            :class="event.net_profit >= 0 ? 'text-green-600' : 'text-red-600'"
                                        >
                                            {{ formatCurrency(event.net_profit) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-600">Sales Overview</h4>
                                <div class="mt-2 space-y-2">
                                    <div>
                                        <span class="text-gray-600">Total Sales:</span>
                                        <span class="ml-2">{{ salesSummary.total_sales }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Total Products:</span>
                                        <span class="ml-2">{{ event.total_products_count }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">FOC Value:</span>
                                        <span class="ml-2">{{ formatCurrency(salesSummary.total_foc_value) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales and Expenses -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sales Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Sales Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Sales</span>
                                    <span class="font-medium">{{ salesSummary.total_sales }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Gross Revenue</span>
                                    <span class="font-medium">{{ formatCurrency(salesSummary.total_revenue) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Discounts</span>
                                    <span class="font-medium">{{ formatCurrency(salesSummary.total_discounts) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">FOC Value</span>
                                    <span class="font-medium">{{ formatCurrency(salesSummary.total_foc_value) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t">
                                    <span class="font-medium">Net Revenue</span>
                                    <span class="font-medium text-green-600">
                                        {{ formatCurrency(salesSummary.total_revenue - salesSummary.total_discounts) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Expense Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Expenses</span>
                                    <span class="font-medium">{{ expenseSummary.total_expenses }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Amount</span>
                                    <span class="font-medium">{{ formatCurrency(expenseSummary.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Shared Expenses</span>
                                    <span class="font-medium">{{ expenseSummary.shared_expenses_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Shared Amount</span>
                                    <span class="font-medium">{{ formatCurrency(expenseSummary.shared_expenses_amount) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t">
                                    <span class="font-medium">Direct Expenses</span>
                                    <span class="font-medium text-red-600">
                                        {{ formatCurrency(expenseSummary.total_amount - expenseSummary.shared_expenses_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Performance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Seller Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discounts</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">FOC Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="seller in sellerPerformance" :key="seller.seller_id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ seller.seller.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ seller.total_sales }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ formatCurrency(seller.total_revenue) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ formatCurrency(seller.total_discounts) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ formatCurrency(seller.total_foc_value) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Product Performance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Product Performance</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">FOC Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in productPerformance" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ product.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ product.total_quantity || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ formatCurrency(product.total_revenue) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ product.foc_quantity || 0 }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FOC Analysis -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">FOC Analysis</h3>
                        <div class="space-y-6">
                            <div v-for="sale in focAnalysis" :key="sale.sale_id" class="border-b pb-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-medium">Sale #{{ sale.sale_id }}</span>
                                        <span class="text-sm text-gray-600 ml-2">by {{ sale.seller }}</span>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ formatDate(sale.date) }}</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="(item, index) in sale.items" :key="index">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ item.product }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {{ formatCurrency(item.value) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ item.reason }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
