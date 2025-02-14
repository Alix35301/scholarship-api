<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps({
    sale: Object
});

const { flash } = useFlash();

const updateStatus = (status) => {
    router.patch(route('sales.update-status', props.sale.id), {
        status: status
    });
};
</script>

<template>
    <Head title="View Sale" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">View Sale #{{ sale.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ flash.message }}
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Sale Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <span class="font-medium">Status:</span>
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-sm ml-2': true,
                                                'bg-yellow-100 text-yellow-800': sale.status === 'pending',
                                                'bg-green-100 text-green-800': sale.status === 'completed',
                                                'bg-red-100 text-red-800': sale.status === 'cancelled'
                                            }"
                                        >
                                            {{ sale.status }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Total Items:</span>
                                        <span class="ml-2">
                                            {{ sale.items.reduce((sum, item) => sum + item.quantity, 0) }}
                                            <span class="text-sm text-gray-500" v-if="sale.items.some(item => item.is_foc)">
                                                ({{ sale.items.filter(item => !item.is_foc).reduce((sum, item) => sum + item.quantity, 0) }} paid,
                                                {{ sale.items.filter(item => item.is_foc).reduce((sum, item) => sum + item.quantity, 0) }} FOC)
                                            </span>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Total Amount:</span>
                                        <span class="ml-2">${{ sale.total_amount }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Total FOC Value:</span>
                                        <span class="ml-2">${{ sale.total_foc_amount }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Notes:</span>
                                        <p class="mt-1">{{ sale.notes || 'No notes' }}</p>
                                    </div>
                                </div>

                                <h3 class="text-lg font-semibold mt-8 mb-4">Items</h3>
                                <div class="space-y-4">
                                    <div v-for="item in sale.items" :key="item.id" class="border p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <span class="font-medium">Product:</span>
                                                <span class="ml-2">{{ item.product.name }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Quantity:</span>
                                                <span class="ml-2">{{ item.quantity }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium">Unit Price:</span>
                                                <span class="ml-2">${{ item.unit_price }}</span>
                                            </div>
                                            <div v-if="!item.is_foc">
                                                <span class="font-medium">Discount:</span>
                                                <span class="ml-2">${{ item.discount }}</span>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="font-medium">{{ item.is_foc ? 'FOC Value' : 'Subtotal' }}:</span>
                                                <span class="ml-2">${{ item.is_foc ? (item.quantity * item.unit_price) : item.subtotal }}</span>
                                            </div>
                                            <div v-if="item.is_foc" class="col-span-2">
                                                <span class="font-medium">FOC Reason:</span>
                                                <p class="mt-1">{{ item.foc_reason }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-4">Related Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <span class="font-medium">Seller:</span>
                                        <span class="ml-2">{{ sale.seller.name }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Created At:</span>
                                        <span class="ml-2">{{ new Date(sale.created_at).toLocaleString() }}</span>
                                    </div>
                                    <div v-if="sale.completed_at">
                                        <span class="font-medium">Completed At:</span>
                                        <span class="ml-2">{{ new Date(sale.completed_at).toLocaleString() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-4" v-if="sale.status === 'pending'">
                            <button
                                @click="updateStatus('completed')"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            >
                                Mark as Completed
                            </button>
                            <button
                                @click="updateStatus('cancelled')"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Cancel Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
