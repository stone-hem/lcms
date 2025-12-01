<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';

interface Lawyer {
  id: number;
  name: string;
  total_cases: number;
  stage_breakdown: Array<{ stage_name: string; count: number }>;
}

interface Props {
  lawyers: Lawyer[];
  case_stages: Array<{ id: number; name: string }>;
  filters: {
    start_date: string;
    end_date: string;
    all_time: boolean;
  };
}

const props = defineProps<Props>();

// Local filter state
const filters = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  all_time: props.filters.all_time || false,
});

watch(() => filters.value.all_time, (val) => {
  if (val) {
    filters.value.start_date = '';
    filters.value.end_date = '';
  }
});

const applyFilters = () => {
  router.get('/cases_by_lawyer_report', filters.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const exportToCSV = () => {
  const params = new URLSearchParams({
    start_date: filters.value.start_date,
    end_date: filters.value.end_date,
    all_time: filters.value.all_time ? '1' : '0',
    export: 'csv'
  });

  window.open(location.pathname + '?' + params.toString(), '_blank');
};
</script>

<template>
  <AppLayout>
    <Head title="Cases by Lawyer Report" />

    <div class="flex flex-col space-y-8 p-4 md:p-8">
      <HeadingSmall
        title="Cases by Lawyer Report"
        description="Breakdown of cases handled by each lawyer with stage distribution"
      />

      <!-- Filters -->
      <Card class="rounded-xl border p-6 shadow-sm">
        <CardContent class="p-0">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label>Start Date</Label>
              <Input type="date" v-model="filters.start_date" :disabled="filters.all_time" />
            </div>
            <div class="space-y-2">
              <Label>End Date</Label>
              <Input type="date" v-model="filters.end_date" :disabled="filters.all_time" />
            </div>
            <div class="flex items-end space-x-3">
              <div class="flex items-center space-x-2">
                <input type="checkbox" id="all_time" v-model="filters.all_time" />
                <Label for="all_time">All Time</Label>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
            <Button variant="outline" @click="applyFilters">Apply Filters</Button>
            <Button variant="secondary" @click="exportToCSV">
              Export CSV
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Table -->
      <Card class="rounded-xl border shadow-sm overflow-hidden">
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="sticky left-0 bg-background">Lawyer</TableHead>
                  <TableHead class="text-center">Total Cases</TableHead>
                  <TableHead v-for="stage in case_stages" :key="stage.id" class="text-center">
                    {{ stage.name }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="lawyer in lawyers" :key="lawyer.id">
                  <TableCell class="sticky left-0 bg-background font-medium">
                    {{ lawyer.name }}
                  </TableCell>
                  <TableCell class="text-center font-semibold">
                    {{ lawyer.total_cases }}
                  </TableCell>
                  <TableCell v-for="breakdown in lawyer.stage_breakdown" :key="breakdown.stage_name" class="text-center">
                    {{ breakdown.count }}
                  </TableCell>
                </TableRow>
                <TableRow v-if="lawyers.length === 0">
                  <TableCell :colspan="2 + case_stages.length" class="text-center py-8 text-muted-foreground">
                    No lawyers or cases found for the selected period.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<style scoped>
.sticky {
  position: sticky;
  z-index: 10;
}
</style>