<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import { Calendar, RefreshCw, Menu, CheckSquare, Activity, CalendarDays } from 'lucide-vue-next'
import MyCalendar from '@/components/calendar/MyCalendar.vue'

import AddEventModal from '@/components/calendar/AddEventModal.vue'
import EditEventModal from '@/components/calendar/EditEventModal.vue'
import ViewEventModal from '@/components/calendar/ViewEventModal.vue'  

interface CalendarEvent {
  id: string
  title: string
  start: string
  end?: string
  start_date?: string
  end_date?: string
  color: 'teal' | 'green' | 'brown'
  description?: string
  priority?: number
  participants?: any[]
  attachments?: any[]
  legal_case?: any
  category?: any
}

const props = defineProps<{
  tasks: CalendarEvent[]
  activities: CalendarEvent[]
  events: CalendarEvent[],
  current_date_range?: {
    start_date: string
    end_date: string
  }
}>()

const loading = ref(false)
const width = ref(0)
const mobileSidebarOpen = ref(false)

const typeFilter = ref(-1)
const caseFilter = ref(-1)

const filters = [
  { name: 'Tasks', value: 1, icon: CheckSquare, color: 'teal' },
  { name: 'Activities', value: 2, icon: Activity, color: 'green' },
  { name: 'Events', value: 3, icon: CalendarDays, color: 'brown' },
]

const allEvents = computed(() => {
  let list: CalendarEvent[] = []

  if (typeFilter.value === -1 || typeFilter.value === 1) list.push(...props.tasks)
  if (typeFilter.value === -1 || typeFilter.value === 2) list.push(...props.activities)
  if (typeFilter.value === -1 || typeFilter.value === 3) list.push(...props.events)

  if (caseFilter.value !== -1) {
    list = list.filter(e => e.legal_case?.id === caseFilter.value)
  }

  return list
})

// Modal States
const showAdd = ref(false)
const showEdit = ref(false)
const showView = ref(false)
const selectedEvent = ref<CalendarEvent | null>(null)
const selectedDate = ref('')

const openAddModal = (date: string) => {
  selectedDate.value = date
  showAdd.value = true
}

const openEditModal = (event: CalendarEvent) => {
  selectedEvent.value = event
  showEdit.value = true
}

const openViewModal = (event: CalendarEvent) => {
  selectedEvent.value = event
  showView.value = true
}

const refresh = (startDate: string | null = null, endDate: string | null = null) => {
  const params: any = {}
  
  if (startDate) params.start_date = startDate
  if (endDate) params.end_date = endDate
  if (caseFilter.value !== -1) params.case_id = caseFilter.value
  
}

onMounted(() => {
  width.value = window.innerWidth
  window.addEventListener('resize', () => width.value = window.innerWidth)
})

watch([typeFilter, caseFilter], () => {
  if (width.value < 1280) mobileSidebarOpen.value = false
  // Refresh when filters change to apply server-side filtering
  refresh()
})
</script>

<template>
  <AppLayout>
    <Head :title="'Case Calendar'" />

    <div class="flex md:space-x-5 app_height overflow-hidden relative md:p-8 p-4">
      <!-- Sidebar -->
      <div
        class="transition-all duration-300 z-[999] bg-white dark:bg-black h-full shadow-lg"
        :class="width < 1280 ? (mobileSidebarOpen ? 'absolute inset-0 w-[280px]' : '-left-full') : 'w-[280px]'"
      >
        <Card bodyClass="p-6 h-full flex flex-col">
          <div class="flex-1 overflow-y-auto">
            <div class="space-y-3">
              <!-- Calendar Type Badge -->
              <div class="px-4 py-2 bg-primary-100 dark:bg-primary-900 rounded-lg text-center">
                <span class="text-primary-700 dark:text-primary-300 font-medium">
                    Case Calender
                </span>
              </div>

              <!-- All -->
              <button
                @click="typeFilter = -1"
                class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 transition"
                :class="typeFilter === -1 ? 'bg-primary-500 text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-700'"
              >
                <Calendar class="w-5 h-5" />
                <span class="font-medium">All Events</span>
              </button>

              <!-- Filters -->
              <button
                v-for="f in filters"
                :key="f.value"
                @click="typeFilter = f.value"
                class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 transition"
                :class="typeFilter === f.value ? 'bg-primary-500 text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-700'"
              >
                <component :is="f.icon" class="w-5 h-5" />
                <span class="font-medium">{{ f.name }}</span>
              </button>

              <!-- Divider -->
              <div class="my-6 border-t border-slate-200 dark:border-slate-700"></div>
              <p class="text-xs text-slate-500 px-4 mb-2">Filter by Case</p>

              <button
                @click="caseFilter = -1"
                class="w-full text-left px-4 py-2.5 rounded-lg text-sm transition"
                :class="caseFilter === -1 ? 'bg-primary-500 text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-700'"
              >
                All Cases
              </button>

              <!-- <button
                v-for="c in props.presets.legal_cases"
                :key="c.id"
                @click="caseFilter = c.id"
                class="w-full text-left px-4 py-2.5 rounded-lg text-sm truncate transition hover:bg-slate-100 dark:hover:bg-slate-700"
                :class="caseFilter === c.id ? 'bg-primary-500 text-white' : ''"
              >
                {{ c.case_number }} - {{ c.title }}
              </button> -->
            </div>
          </div>
        </Card>
      </div>

      <!-- Mobile Overlay -->
      <div
        v-if="width < 1280 && mobileSidebarOpen"
        class="fixed inset-0 bg-black bg-opacity-50 z-[998]"
        @click="mobileSidebarOpen = false"
      />

      <!-- Main Content -->
      <div class="flex-1">
        <Card bodyClass="p-0 h-full">
          <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold">Case Calender</h3>
            <div class="flex items-center gap-3">
              <!-- Loading Spinner (Tailwind) -->
              <div v-if="loading" class="w-9 h-9 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>

              <Button text="Refresh" btnClass="btn-outline-primary" @click="refresh()">
                <RefreshCw class="w-5 h-5" />
              </Button>

              <!-- Mobile Menu -->
              <button
                v-if="width < 1280"
                @click="mobileSidebarOpen = true"
                class="lg:hidden w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700"
              >
                <Menu class="w-5 h-5" />
              </button>
            </div>
          </div>

          <div class="h-[calc(100%-60px)]">
            <MyCalendar
              :events="allEvents"
              @dateClick="(info) => openAddModal(info.dateStr)"
              @eventClick="openViewModal"
              @eventDrop="refresh"
              @eventResize="refresh"
            />
          </div>
        </Card>
      </div>
    </div>

    <!-- MODALS -->
    <AddEventModal
      :open="showAdd"
      :presets="[]"
      :initial-date="selectedDate"
      @close="showAdd = false"
      @success="refresh(); showAdd = false"
    />

    <EditEventModal
      :open="showEdit"
      :event="selectedEvent"
      :presets="[]"
      @close="showEdit = false"
      @success="refresh(); showEdit = false"
    />

    <ViewEventModal
      :open="showView"
      :item="selectedEvent"
      @close="showView = false"
      @edit="showView = false; showEdit = true"
    />
  </AppLayout>
</template>