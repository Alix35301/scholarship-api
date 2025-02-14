<template>
    <AuthenticatedLayout :title="`${event.name} - Expense Details`">
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events', url: route('events.index') },
                        { label: event.name, url: route('events.show', event.id) },
                        { label: 'Expenses', url: route('events.expenses.index', event.id) },
                        { label: expense.title }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ event.name }} - Expense Details
                    </h2>
                    <Link
                        :href="route('events.expenses.index', event.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Back to Expenses
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Title</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ expense.title }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ expense.description || 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                        <dd class="mt-1 text-sm text-gray-900">${{ expense.amount }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ expense.date }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                                        <dd class="mt-1">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-blue-100 text-blue-800': expense.type === 'individual',
                                                'bg-purple-100 text-purple-800': expense.type === 'shared'
                                            }">
                                                {{ expense.type }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-yellow-100 text-yellow-800': expense.status === 'pending',
                                                'bg-green-100 text-green-800': expense.status === 'approved',
                                                'bg-red-100 text-red-800': expense.status === 'rejected'
                                            }">
                                                {{ expense.status }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ expense.creator.name }} ({{ expense.creator.email }})
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ expense.created_at }}</dd>
                                    </div>
                                    <div v-if="expense.receipt_path">
                                        <dt class="text-sm font-medium text-gray-500">Receipt</dt>
                                        <dd class="mt-1">
                                            <a
                                                :href="'/storage/' + expense.receipt_path"
                                                target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View Receipt
                                            </a>
                                        </dd>
                                    </div>
                                    <div v-if="expense.status === 'rejected'">
                                        <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ expense.rejection_reason }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Shared Expense Allocations -->
                        <div v-if="expense.type === 'shared'" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Share Allocations</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Share Percentage</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Share Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="allocation in expense.allocations" :key="allocation.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ allocation.user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ allocation.user.email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ allocation.share_percentage }}%</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">${{ allocation.share_amount }}</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Status Update Form -->
                        <div v-if="expense.status === 'pending'" class="mt-8 border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                            <form @submit.prevent="updateStatus" class="space-y-4">
                                <div>
                                    <InputLabel for="status" value="New Status" />
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                    </select>
                                </div>

                                <div v-if="form.status === 'rejected'">
                                    <InputLabel for="rejection_reason" value="Rejection Reason" />
                                    <textarea
                                        id="rejection_reason"
                                        v-model="form.rejection_reason"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3"
                                        required
                                    ></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <PrimaryButton :disabled="form.processing">
                                        Update Status
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    event: Object,
    expense: Object,
});

const form = useForm({
    status: 'approved',
    rejection_reason: '',
});

const updateStatus = () => {
    form.patch(route('events.expenses.update', [props.event.id, props.expense.id]));
};
</script>
