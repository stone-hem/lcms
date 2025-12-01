<script setup lang="ts">
// Page props coming from Laravel controller
interface Props {
    items: Array<any>;
    total_contingent_liability: number;
    case_stages: Array<any>;
    lawyers: Array<any>;
    nature_of_claims: Array<any>;
    case_types: Array<any>;
    filters: {
        start_date: string;
        end_date: string;
        all_time: boolean;
        case_stage: number;
        nature_of_claim: number;
        case_type: number;
        is_internal: number;
        lawyer_ids: Array<number>;
        has: Array<string>;
        does_not_have: Array<string>;
    };
}

const props = defineProps<Props>();

// Layouts & Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, watch } from 'vue';

// UI components
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Select from '@/components/form/Select.vue';
import MultiSelect from '@/components/form/MultiSelect.vue'; // Your custom multi-select
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

// Filter state
const filters = reactive({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    all_time: props.filters.all_time || false,
    case_stage: props.filters.case_stage?.toString() || '-1',
    nature_of_claim: props.filters.nature_of_claim?.toString() || '-1',
    case_type: props.filters.case_type?.toString() || '-1',
    is_internal: props.filters.is_internal?.toString() || '-1',
    lawyer_ids: props.filters.lawyer_ids || [],
    has: props.filters.has || [],
    does_not_have: props.filters.does_not_have || []
});

// Available filter options
const hasFilters = [
    { id: '1', label: 'DG Approval Attachments' },
    { id: '2', label: 'Procurement Authority Documents' },
    { id: '3', label: 'SLA' },
    { id: '4', label: 'Interim Fee Note' },
    { id: '5', label: 'Judgement Attachments' },
    { id: '6', label: 'Final Fee Note' }
];

// Apply filters
const applyFilters = () => {
    const params = {
        ...filters,
        lawyer_ids: filters.lawyer_ids.join(','),
        has: filters.has.join(','),
        does_not_have: filters.does_not_have.join(',')
    };
    
    router.get('/reports/case-reports', params, {
        preserveState: true,
        preserveScroll: true
    });

};

// Clear filters
const clearFilters = () => {
    Object.assign(filters, {
        start_date: '',
        end_date: '',
        all_time: false,
        case_stage: '-1',
        nature_of_claim: '-1',
        case_type: '-1',
        is_internal: '-1',
        lawyer_ids: [],
        has: [],
        does_not_have: []
    });
    
    applyFilters();
};

// Export to CSV
const exportToCSV = () => {
  const params = new URLSearchParams({
    // Spread simple values
    start_date: filters.start_date,
    end_date: filters.end_date,
    all_time: filters.all_time ? '1' : '0',
    case_stage: filters.case_stage,
    nature_of_claim: filters.nature_of_claim,
    case_type: filters.case_type,
    is_internal: filters.is_internal,

    // Handle arrays → convert to comma-separated strings (or empty if none)
    lawyer_ids: filters.lawyer_ids.length > 0 ? filters.lawyer_ids.join(',') : '',
    has: filters.has.length > 0 ? filters.has.join(',') : '',
    does_not_have: filters.does_not_have.length > 0 ? filters.does_not_have.join(',') : '',

    export: 'csv'
  } as Record<string, string>); // Type assertion to satisfy TS

  // This works 100% without Ziggy or route()
  window.open(location.pathname + '?' + params.toString(), '_blank');
};

// Watch all_time to clear dates when checked
watch(() => filters.all_time, (newVal) => {
    if (newVal) {
        filters.start_date = '';
        filters.end_date = '';
    }
});
</script>

<template>
    <AppLayout>
        <Head title="Case Reports" />

        <div class="flex flex-col space-y-8 md:p-8 p-4">
            <!-- Header Section -->
            <HeadingSmall
                title="Case Reports"
                description="View consolidated reports and contingent liability summaries"
            />

            <!-- Filters Section -->
            <Card class="rounded-xl border p-6 shadow-sm">
                <CardContent class="p-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Date Range -->
                        <div class="space-y-2">
                            <Label for="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                type="date"
                                v-model="filters.start_date"
                                :disabled="filters.all_time"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="end_date">End Date</Label>
                            <Input
                                id="end_date"
                                type="date"
                                v-model="filters.end_date"
                                :disabled="filters.all_time"
                            />
                        </div>

                        <div class="space-y-2 flex items-end">
                            <div class="flex items-center space-x-2">
                                <input
                                    id="all_time"
                                    type="checkbox"
                                    v-model="filters.all_time"
                                />
                                <Label for="all_time">All Time</Label>
                            </div>
                        </div>

                        <!-- Case Type -->
                        <div class="space-y-2">
                            <Label for="case_type">Case Type</Label>
                            <Select v-model="filters.case_type">
                                <option value="-1">All</option>
                                <option
                                    v-for="caseType in case_types"
                                    :key="caseType.id"
                                    :value="caseType.id.toString()"
                                >
                                    {{ caseType.name }}
                                </option>
                            </Select>
                        </div>

                        <!-- Case Stage -->
                        <div class="space-y-2">
                            <Label for="case_stage">Case Stage</Label>
                            <Select v-model="filters.case_stage">
                                <option value="-1">All</option>
                                <option
                                    v-for="stage in case_stages"
                                    :key="stage.id"
                                    :value="stage.id.toString()"
                                >
                                    {{ stage.name }}
                                </option>
                            </Select>
                        </div>

                        <!-- Nature of Claim -->
                        <div class="space-y-2">
                            <Label for="nature_of_claim">Nature of Claim</Label>
                            <Select v-model="filters.nature_of_claim">
                                <option value="-1">All</option>
                                <option
                                    v-for="claim in nature_of_claims"
                                    :key="claim.id"
                                    :value="claim.id.toString()"
                                >
                                    {{ claim.name }}
                                </option>
                            </Select>
                        </div>

                        <!-- Internal/External -->
                        <div class="space-y-2">
                            <Label for="is_internal">Case Type</Label>
                            <Select v-model="filters.is_internal">
                                <option value="-1">All</option>
                                <option value="1">Internal</option>
                                <option value="0">External</option>
                            </Select>
                        </div>

                        <!-- Lawyers -->
                        <div class="space-y-2">
                            <MultiSelect
                                v-model="filters.lawyer_ids"
                                :options="lawyers"
                                value-key="id"
                                label-key="name"
                                placeholder="Select Lawyers"
                                label="Select Lawyers"
                                :return-ids-only="true"
                            />
                        </div>

                        <!-- Has Documents -->
                        <div class="space-y-2">
                            <MultiSelect
                                v-model="filters.has"
                                :options="hasFilters"
                                value-key="id"
                                label-key="label"
                                placeholder="Select documents that exist"
                                label="Has Documents"
                                :return-ids-only="true"
                            />
                        </div>

                        <!-- Missing Documents -->
                        <div class="space-y-2">
                            <MultiSelect
                                v-model="filters.does_not_have"
                                :options="hasFilters"
                                value-key="id"
                                label-key="label"
                                placeholder="Select missing documents"
                                label="Missing Documents"
                                :return-ids-only="true"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 mt-6 pt-6 border-t">
                        <Button variant="outline" @click="clearFilters">
                            Clear Filters
                        </Button>
                        <Button @click="applyFilters">
                            Apply Filters
                        </Button>
                        <Button variant="secondary" @click="exportToCSV" :disabled="items.length === 0">
                            Export CSV
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Card -->
            <Card class="rounded-xl border p-4 shadow-sm">
                <CardContent>
                    <div class="flex flex-col space-y-2">
                        <p class="text-sm text-muted-foreground">
                            Total Contingent Liability
                        </p>
                        <p class="text-3xl font-semibold">
                            {{ total_contingent_liability.toLocaleString() }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ items.length }} case(s) found
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Reports Table -->
            <Card class="rounded-xl border shadow-sm">
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Case Title</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Updated</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <TableCell>{{ item.id }}</TableCell>
                                <TableCell>{{ item.title }}</TableCell>

                                <TableCell>
                                    <Badge variant="outline">
                                        {{ item.case_stage.name }}
                                    </Badge>
                                </TableCell>

                                <TableCell>
                                    {{ Number(item.total_contingent_liability).toLocaleString() }}
                                </TableCell>

                                <TableCell>
                                    {{ item.updated_at }}
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="items.length === 0">
                                <TableCell
                                    colspan="5"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No case reports found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>