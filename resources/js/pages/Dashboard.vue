<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { onMounted, ref, watch, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import VChart from 'vue-echarts'
import type { ECBasicOption } from 'echarts/types/dist/shared'
import 'echarts'
import { 
  Briefcase, 
  Gavel, 
  Users, 
  Building2, 
  FileText,
  TrendingUp,
  Calendar,
  Activity
} from 'lucide-vue-next'

interface PageProps {
  cases_count: Array<{ x_value: string; label: string; y_value: number }>
  cases_by_status: Array<{ x_value: string; label: string; y_value: number }>
  cases_by_nature_of_claim: Array<{ nature_of_claim: string; case_count: number }>
  cases_by_lawyer: Array<{ lawyer_name: string; case_count: number }>
  cases_by_external_advocate: Array<{ firm_name: string; case_count: number }>
  tasks: any[]
}

const props = usePage().props as any as PageProps

// ──────────────────────────────────────────────────────────────
// Computed Totals for Top Cards
// ──────────────────────────────────────────────────────────────
const totalCases = computed(() => 
  props.cases_count.reduce((sum, item) => sum + item.y_value, 0)
)

const totalInternal = computed(() => 
  props.cases_count.filter(c => c.label === 'Internal').reduce((a, b) => a + b.y_value, 0)
)

const totalExternal = computed(() => 
  props.cases_count.filter(c => c.label === 'External').reduce((a, b) => a + b.y_value, 0)
)

const totalLawyers = computed(() => props.cases_by_lawyer.length)
const totalExternalAdvocates = computed(() => props.cases_by_external_advocate.length)
const totalNatures = computed(() => props.cases_by_nature_of_claim.length)

// ──────────────────────────────────────────────────────────────
// ECharts Options (same as before)
// ──────────────────────────────────────────────────────────────
const barChartStatusOption = ref<ECBasicOption>({})
const barChartCountOption = ref<ECBasicOption>({})
const pieStatusOption = ref<ECBasicOption>({})
const pieInternalExternalOption = ref<ECBasicOption>({})

function groupByLabel(data: any[]) {
  const map = new Map<string, { name: string; data: number[] }>()
  const categories = [...new Set(data.map(d => d.x_value))].sort()
  data.forEach(item => {
    if (!map.has(item.label)) {
      map.set(item.label, { name: item.label || 'Unknown', data: new Array(categories.length).fill(0) })
    }
    const idx = categories.indexOf(item.x_value)
    if (idx !== -1) map.get(item.label)!.data[idx] = item.y_value
  })
  return { categories, series: Array.from(map.values()) }
}

function initCharts() {
  const statusData = groupByLabel(props.cases_by_status)
  barChartStatusOption.value = {
    tooltip: { trigger: 'axis' },
    legend: { bottom: 10 },
    grid: { top: 40, bottom: 80, left: 60, right: 20 },
    xAxis: { type: 'category', data: statusData.categories, axisLabel: { rotate: 45, interval: 0 } },
    yAxis: { type: 'value' },
    series: statusData.series.map(s => ({
      name: s.name,
      type: 'bar',
      stack: 'total',
      data: s.data,
      barWidth: '60%',
      itemStyle: { borderRadius: 4 }
    }))
  }

  const countData = groupByLabel(props.cases_count)
  barChartCountOption.value = {
    tooltip: { trigger: 'axis' },
    legend: { bottom: 10 },
    grid: { top: 40, bottom: 80, left: 60, right: 20 },
    xAxis: { type: 'category', data: countData.categories, axisLabel: { rotate: 45, interval: 0 } },
    yAxis: { type: 'value' },
    series: countData.series.map(s => ({
      name: s.name,
      type: 'bar',
      stack: 'total',
      data: s.data,
      barWidth: '60%'
    }))
  }

  const statusSummary = props.cases_by_status.reduce((acc, cur) => {
    const key = cur.label || 'Unknown'
    acc[key] = (acc[key] || 0) + cur.y_value
    return acc
  }, {} as Record<string, number>)

  pieStatusOption.value = {
    tooltip: { trigger: 'item' },
    legend: { orient: 'vertical', left: 'left' },
    series: [{
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
      label: { show: false },
      emphasis: { label: { show: true, fontSize: 20, fontWeight: 'bold' } },
      data: Object.entries(statusSummary).map(([name, value]) => ({ name, value }))
    }]
  }

  pieInternalExternalOption.value = {
    tooltip: { trigger: 'item' },
    legend: { top: '5%', left: 'center' },
    series: [{
      type: 'pie',
      radius: '70%',
      data: [
        { value: totalInternal.value, name: 'Internal Cases' },
        { value: totalExternal.value, name: 'External Cases' }
      ],
      emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)' } }
    }]
  }
}

onMounted(initCharts)
watch(() => props, initCharts, { deep: true })

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: dashboard().url },
]
</script>

<template>
  <Head title="Analytics Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-10">

      <!-- Top Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-medium">Total Cases</p>
              <p class="text-3xl font-bold mt-2">{{ totalCases }}</p>
            </div>
            <Briefcase class="h-12 w-12 text-blue-200 opacity-80" />
          </div>
          <div class="mt-4 flex items-center text-blue-100">
            <TrendingUp class="h-4 w-4 mr-1" />
            <span class="text-xs">All time</span>
          </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 text-sm font-medium">Internal Cases</p>
              <p class="text-3xl font-bold mt-2">{{ totalInternal }}</p>
            </div>
            <Gavel class="h-12 w-12 text-green-200 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 text-sm font-medium">External Cases</p>
              <p class="text-3xl font-bold mt-2">{{ totalExternal }}</p>
            </div>
            <Building2 class="h-12 w-12 text-purple-200 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100 text-sm font-medium">Active Lawyers</p>
              <p class="text-3xl font-bold mt-2">{{ totalLawyers }}</p>
            </div>
            <Users class="h-12 w-12 text-orange-200 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-pink-500 to-rose-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-pink-100 text-sm font-medium">External Advocates</p>
              <p class="text-3xl font-bold mt-2">{{ totalExternalAdvocates }}</p>
            </div>
            <FileText class="h-12 w-12 text-pink-200 opacity-80" />
          </div>
        </div>

        <div class="bg-gradient-to-r from-cyan-500 to-teal-600 text-white rounded-xl p-6 shadow-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-cyan-100 text-sm font-medium">Nature of Claims</p>
              <p class="text-3xl font-bold mt-2">{{ totalNatures }}</p>
            </div>
            <Activity class="h-12 w-12 text-cyan-200 opacity-80" />
          </div>
        </div>
      </div>

      <!-- Charts & Tables (same as before) -->
      <div class="space-y-10">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Stacked Bar: Cases by Status -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Cases by Status (Over Time)</h2>
          <v-chart :option="barChartStatusOption" autoresize style="height: 420px" />
        </div>

        <!-- Stacked Bar: Internal vs External -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Internal vs External Cases (Over Time)</h2>
          <v-chart :option="barChartCountOption" autoresize style="height: 420px" />
        </div>
      </div>

        <!-- Pie Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Case Status Distribution</h2>
            <v-chart :option="pieStatusOption" autoresize style="height: 400px" />
          </div>
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Internal vs External Split</h2>
            <v-chart :option="pieInternalExternalOption" autoresize style="height: 400px" />
          </div>
        </div>

        <!-- Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Nature of Claim -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg overflow-hidden">
            <h2 class="text-xl font-semibold p-6 bg-gray-50 dark:bg-gray-800">Cases by Nature of Claim</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th class="px-6 py-3 text-left font-medium">Nature</th>
                    <th class="px-6 py-3 text-right font-medium">Count</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in props.cases_by_nature_of_claim" :key="item.nature_of_claim" class="border-t dark:border-gray-700">
                    <td class="px-6 py-4">{{ item.nature_of_claim || 'Unknown' }}</td>
                    <td class="px-6 py-4 text-right font-medium">{{ item.case_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Internal Lawyers -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg overflow-hidden">
            <h2 class="text-xl font-semibold p-6 bg-gray-50 dark:bg-gray-800">Top Internal Lawyers</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th class="px-6 py-3 text-left font-medium">Lawyer</th>
                    <th class="px-6 py-3 text-right font-medium">Cases</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in props.cases_by_lawyer" :key="item.lawyer_name" class="border-t dark:border-gray-700">
                    <td class="px-6 py-4">{{ item.lawyer_name.trim() || 'Unnamed' }}</td>
                    <td class="px-6 py-4 text-right font-medium">{{ item.case_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- External Advocates -->
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg overflow-hidden">
            <h2 class="text-xl font-semibold p-6 bg-gray-50 dark:bg-gray-800">Top External Advocates</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th class="px-6 py-3 text-left font-medium">Advocate / Firm</th>
                    <th class="px-6 py-3 text-right font-medium">Cases</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in props.cases_by_external_advocate" :key="item.firm_name" class="border-t dark:border-gray-700">
                    <td class="px-6 py-4">{{ item.firm_name.trim() || 'Unnamed' }}</td>
                    <td class="px-6 py-4 text-right font-medium">{{ item.case_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>