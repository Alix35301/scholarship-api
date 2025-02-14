<template>
    <Head title="Add Expense" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events', url: route('events.index') },
                        { label: event.name, url: route('events.dashboard', event.id) },
                        { label: 'Expenses', url: route('events.expenses.index', event.id) },
                        { label: 'Add Expense' }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Expense to {{ event.name }}</h2>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            @click="showCsvModal = true"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 text-sm"
                        >
                            Upload CSV
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="title" value="Title" />
                                <TextInput
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.title" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <TextArea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full"
                                    rows="4"
                                />
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="amount" value="Amount (RF)" />
                                    <TextInput
                                        id="amount"
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.amount" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="date" value="Date" />
                                    <TextInput
                                        id="date"
                                        v-model="form.date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.date" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="type" value="Type" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="individual">Individual</option>
                                    <option value="shared">Shared</option>
                                </select>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>

                            <!-- Individual Expense User Selection -->
                            <div v-if="form.type === 'individual'" class="space-y-4">
                                <div>
                                    <InputLabel for="user_id" value="Select User" />
                                    <select
                                        id="user_id"
                                        v-model="form.user_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">Select a user</option>
                                        <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                                            {{ seller.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.user_id" class="mt-2" />
                                </div>
                            </div>

                            <!-- Shared Expense Allocations -->
                            <div v-if="form.type === 'shared'" class="space-y-4">
                                <div>
                                    <InputLabel for="allocation_type" value="Allocation Method" />
                                    <select
                                        id="allocation_type"
                                        v-model="form.allocation_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="equal">Divide Equally</option>
                                        <option value="custom">Custom Percentages</option>
                                    </select>
                                </div>

                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium">Share Allocations</h3>
                                    <div class="flex items-center gap-4">
                                        <span class="text-sm" :class="totalPercentage == 100 ? 'text-green-600' : 'text-red-600'">
                                            Total: {{ totalPercentage }}%
                                        </span>
                                        <button
                                            type="button"
                                            @click="addAllSellers"
                                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                                        >
                                            Add All Sellers
                                        </button>
                                        <button
                                            type="button"
                                            @click="addAllocation"
                                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm"
                                        >
                                            Add Single Seller
                                        </button>
                                    </div>
                                </div>

                                <div v-for="(allocation, index) in form.allocations" :key="index" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border rounded-lg">
                                    <div>
                                        <InputLabel :for="'user_id_' + index" value="Seller" />
                                        <select
                                            :id="'user_id_' + index"
                                            v-model="allocation.user_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="">Select a seller</option>
                                            <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                                                {{ seller.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors['allocations.' + index + '.user_id']" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div class="flex-1">
                                            <template v-if="form.allocation_type === 'custom'">
                                                <InputLabel :for="'share_percentage_' + index" value="Share Percentage" />
                                                <TextInput
                                                    :id="'share_percentage_' + index"
                                                    v-model="allocation.share_percentage"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    class="mt-1 block w-full"
                                                    required
                                                />
                                                <InputError :message="form.errors['allocations.' + index + '.share_percentage']" class="mt-2" />
                                            </template>
                                            <template v-else>
                                                <InputLabel :for="'share_amount_' + index" value="Share Amount" />
                                                <div class="mt-1 text-sm">
                                                    <div class="font-medium">RF {{ getShareAmount(allocation) }}</div>
                                                    <div class="text-gray-500">{{ getEqualShare(form.allocations.length) }}% of total</div>
                                                </div>
                                            </template>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeAllocation(index)"
                                            class="mt-6 text-red-600 hover:text-red-800"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div v-if="form.allocation_type === 'equal' && form.allocations.length > 0" class="text-sm text-gray-600">
                                    Each seller will receive RF {{ getShareAmount(form.allocations[0]) }} ({{ getEqualShare(form.allocations.length) }}% of RF {{ form.amount || 0 }})
                                </div>
                            </div>

                            <div>
                                <InputLabel for="receipt" value="Receipt (Optional)" />
                                <input
                                    type="file"
                                    id="receipt"
                                    @input="form.receipt = $event.target.files[0]"
                                    accept="image/*,application/pdf"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.receipt" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('events.expenses.index', event.id)"
                                    class="text-gray-600 hover:text-gray-900 mr-4"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :disabled="form.processing">
                                    Create Expense
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- CSV Upload Modal -->
        <Modal :show="showCsvModal" @close="showCsvModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Upload Expenses CSV
                </h2>

                <div class="mt-6">
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="csv_file" value="CSV File" />
                            <input
                                type="file"
                                id="csv_file"
                                @input="csvForm.csv_file = $event.target.files[0]"
                                accept=".csv"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="csvForm.errors.csv_file" class="mt-2" />
                        </div>

                        <div class="text-sm text-gray-600">
                            <p class="mb-2">CSV file should contain the following columns:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>title (required)</li>
                                <li>description (optional)</li>
                                <li>amount (required)</li>
                                <li>date (required, YYYY-MM-DD format)</li>
                                <li>type (required, 'individual' or 'shared')</li>
                                <li>seller_ids (required for shared expenses, comma-separated IDs)</li>
                            </ul>
                            <div class="mt-4">
                                <button
                                    type="button"
                                    @click="downloadTemplate"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Download Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <SecondaryButton @click="showCsvModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        :disabled="csvForm.processing"
                        @click="uploadCsv"
                    >
                        Upload
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
    event: Object,
    sellers: Array,
});

const form = useForm({
    title: '',
    description: '',
    amount: '',
    date: '',
    type: 'individual',
    user_id: '',
    allocation_type: 'equal',
    receipt: null,
    allocations: [],
});

// Add new refs and forms for CSV handling
const showCsvModal = ref(false);
const csvForm = useForm({
    csv_file: null,
});

const getEqualShare = (count) => {
    return count > 0 ? (100 / count).toFixed(2) : 0;
};

const getShareAmount = (allocation) => {
    if (!form.amount) return 0;
    const percentage = form.allocation_type === 'equal'
        ? getEqualShare(form.allocations.length)
        : allocation.share_percentage;
    return (Math.round((Number(form.amount) * Number(percentage)) / 100 * 100) / 100).toFixed(2);
};

const addAllocation = () => {
    form.allocations.push({
        user_id: '',
        share_percentage: ''
    });
    if (form.allocation_type === 'equal') {
        divideEqually();
    }
};

const removeAllocation = (index) => {
    form.allocations.splice(index, 1);
    if (form.allocation_type === 'equal') {
        divideEqually();
    }
};

const divideEqually = () => {
    const count = form.allocations.length;
    if (count > 0) {
        const equalShare = (100 / count).toFixed(2);
        const lastShare = (100 - (equalShare * (count - 1))).toFixed(2);

        form.allocations.forEach((allocation, index) => {
            // Give the last seller any remaining percentage to ensure total is exactly 100%
            allocation.share_percentage = index === count - 1 ? lastShare : equalShare;
        });
    }
};

const addAllSellers = () => {
    // Clear existing allocations
    form.allocations = [];

    // Add all active sellers
    props.sellers.forEach(seller => {
        form.allocations.push({
            user_id: seller.id,
            share_percentage: ''
        });
    });

    // Divide equally
    divideEqually();
};

const submit = () => {
    if (form.type === 'shared') {
        if (form.allocation_type === 'equal') {
            divideEqually();
        }

        // Verify total percentage is 100%
        const total = form.allocations.reduce((sum, allocation) =>
            sum + Number(allocation.share_percentage), 0);

        if (Math.abs(total - 100) > 0.01) {
            alert('Total share percentage must equal 100%');
            return;
        }
    } else if (form.type === 'individual' && !form.user_id) {
        alert('Please select a user for individual expense');
        return;
    }

    form.post(route('events.expenses.store', props.event.id));
};

const totalPercentage = computed(() => {
    if (form.type !== 'shared') return 0;
    return form.allocations.reduce((total, allocation) => {
        return total + Number(allocation.share_percentage || 0);
    }, 0).toFixed(2);
});

const uploadCsv = () => {
    csvForm.post(route('events.expenses.import', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCsvModal.value = false;
            csvForm.reset();
        },
    });
};

const downloadTemplate = () => {
    // Create CSV template content
    const headers = ['title', 'description', 'amount', 'date', 'type', 'seller_ids'];
    const template = [
        headers.join(','),
        'Office Supplies,Monthly office supplies,1000,2024-02-20,individual,1',
        'Venue Rental,Event venue payment,5000,2024-02-21,shared,"1,2,3"'
    ].join('\n');

    // Create and trigger download
    const blob = new Blob([template], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'expense_template.csv';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
};
</script>
