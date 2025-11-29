<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AddCaseModal from '@/components/cases/AddCaseModal.vue';
import DeleteCaseModal from '@/components/cases/DeleteCaseModal.vue';
import EditCaseModal from '@/components/cases/EditCaseModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Plus } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Link from '@inertiajs/vue3';

interface Props {
    items: Array<any>;
    total_count: number;
    presets: object;
}
const props = defineProps<Props>();

const searchQuery = ref('');
const selectedCase = ref<any>(null);

// Modal visibility
const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

// Open modals
const openAdd = () => (showAdd.value = true);
const openEdit = (item: any) => {
    selectedCase.value = item;
    showEdit.value = true;
};
const openDelete = (item: any) => {
    selectedCase.value = item;
    showDelete.value = true;
};

// Close all modals on success
const closeAll = () => {
    showAdd.value = showEdit.value = showDelete.value = false;
    selectedCase.value = null;
};

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items;
    const q = searchQuery.value.toLowerCase();
    return props.items.filter(
        (item) =>
            item.title?.toLowerCase().includes(q) ||
            item.case_number?.toLowerCase().includes(q) ||
            item.status?.toLowerCase().includes(q),
    );
});

const formatDate = (date: string) =>
    date ? new Date(date).toLocaleDateString() : 'N/A';
const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);

const getStatusVariant = (status: string) => {
    const map: Record<string, string> = {
        pending: 'secondary',
        active: 'default',
        closed: 'outline',
        won: 'default',
        lost: 'destructive',
    };
    return map[status] || 'outline';
};
</script>

<template>
    <AppLayout>
        <Head title="Legal Cases" />

        <div class="flex flex-col space-y-8 p-8">
            <!-- Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <HeadingSmall
                    title="Legal Cases"
                    description="View consolidated reports and contingent liability summaries"
                />

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <div class="relative">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search cases..."
                            class="w-full pl-10 sm:w-64"
                        />
                    </div>
                    <Button @click="openAdd">
                        <Plus />
                        Add Case
                    </Button>
                </div>
            </div>

            <!-- Table -->
            <Card class="rounded-xl border shadow-sm">
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Case Title</TableHead>
                                <TableHead>Case Number</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Updated</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in filteredItems"
                                :key="item.id"
                            >
                                <TableCell>{{ item.id }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{
                                            item.title
                                        }}</span>
                                        <span class="text-sm text-gray-500">{{
                                            item.case_type.name
                                        }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{
                                    item.case_number || 'N/A'
                                }}</TableCell>
                                <TableCell>
                                    <Badge>
                                        {{ item.case_stage.name }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{
                                    formatCurrency(Number(item.contingent_liability.amount))
                                }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{
                                        item.is_internal
                                            ? 'Internal'
                                            : 'External'
                                    }}</Badge>
                                </TableCell>
                                <TableCell>{{
                                    formatDate(item.updated_at)
                                }}</TableCell>
                                <TableCell>
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('case.show', item.id)">
                                            <Button variant="outline" size="sm">
                                                View
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="openEdit(item)"
                                            >Edit</Button
                                        >
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="openDelete(item)"
                                            >Delete</Button
                                        >
                                    </div>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="filteredItems.length === 0">
                                <TableCell
                                    colspan="8"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center space-y-3"
                                    >
                                        <p class="text-lg font-medium">
                                            No cases found
                                        </p>
                                        <Button @click="openAdd"
                                            >Add Your First Case</Button
                                        >
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <div class="flex justify-between text-sm text-gray-500">
                <span
                    >Showing {{ filteredItems.length }} of
                    {{ props.total_count }} cases</span
                >
                <span v-if="searchQuery" class="text-blue-600"
                    >Filtered by: "{{ searchQuery }}"</span
                >
            </div>
        </div>

        <!-- Modals -->
        <AddCaseModal
            :open="showAdd"
            @close="showAdd = false"
            @success="closeAll"
            :presets="presets"
        />
        <EditCaseModal
            :open="showEdit"
            :case="selectedCase"
            @close="closeAll"
            @success="closeAll"
            :presets="presets"
        />
        <DeleteCaseModal
            :open="showDelete"
            :case="selectedCase"
            @close="closeAll"
            @success="closeAll"
        />
    </AppLayout>
</template>
