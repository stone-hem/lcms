<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import TaskCard from '@/components/tasks/TaskCard.vue'
import AddTaskModal from '@/components/tasks/AddTaskModal.vue'
import EditTaskModal from '@/components/tasks/EditTaskModal.vue'
import ViewTaskModal from '@/components/tasks/ViewTaskModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Menu, Search, Plus, Filter, Star, CheckCircle2, Clock, AlertCircle } from 'lucide-vue-next'

const props = defineProps<{
  tasks: any
  filters: any
  status_counts: Record<string, number>
}>()

const search = ref(props.filters.s || '')
const statusFilter = ref(props.filters.status || 'all')
const showAdd = ref(false)
const showEdit = ref(false)
const showView = ref(false)
const selectedTask = ref<any>(null)

const sidebarOpen = ref(false)

const filters = [
  { value: 'all', label: 'All Tasks', icon: Filter, color: 'gray' },
  { value: 'open', label: 'Open', icon: AlertCircle, color: 'blue' },
  { value: 'in_progress', label: 'In Progress', icon: Clock, color: 'yellow' },
  { value: 'completed', label: 'Completed', icon: CheckCircle2, color: 'green' },
  { value: 'favourite', label: 'Favourites', icon: Star, color: 'purple' },
]

watch([search, statusFilter], () => {
  router.get('/tasks', {
    s: search.value || null,
    status: statusFilter.value === 'all' ? null : statusFilter.value,
  }, { preserveState: true, replace: true })
})

const openView = (task: any) => {
  selectedTask.value = task
  showView.value = true
}
</script>

<template>
  <AppLayout>
    <Head title="My Tasks" />

    <div class="flex h-full overflow-hidden">
      <!-- Sidebar -->
      <div :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-black border-r transform transition-transform lg:translate-x-0 lg:static lg:inset-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]">
        <div class="flex flex-col h-full">
          <div class="p-6 border-b">
            <Button @click="showAdd = true" class="w-full">
              <Plus class="w-4 h-4 mr-2" /> Add Task
            </Button>
          </div>

          <nav class="flex-1 px-4 py-4 space-y-1">
            <button
              v-for="filter in filters"
              :key="filter.value"
              @click="statusFilter = filter.value"
              class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition"
              :class="statusFilter === filter.value ? 'bg-blue-50 text-blue-700 dark:bg-blue-50 dark:text-blue-700' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
            >
              <div class="flex items-center space-x-3">
                <component :is="filter.icon" class="w-5 h-5" :class="`text-${filter.color}-600`" />
                <span>{{ filter.label }}</span>
              </div>
              <span class="text-sm font-medium">{{ status_counts[filter.value] || 0 }}</span>
            </button>
          </nav>
        </div>
      </div>

      <!-- Mobile overlay -->
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden" @click="sidebarOpen = false" />

      <!-- Main Content -->
      <div class="flex-1 overflow-y-auto">
        <div class="p-6 space-y-6">
          <div class="flex items-center justify-between">
            <HeadingSmall title="My Tasks" description="Manage your assigned and created tasks" />

            <div class="flex items-center space-x-4">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search tasks..."
                  class="pl-10 pr-4 py-2 border rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <button @click="sidebarOpen = true" class="lg:hidden">
                <Menu class="w-6 h-6" />
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <TaskCard
              v-for="task in tasks.data"
              :key="task.id"
              :task="task"
              @click="openView(task)"
            />
          </div>

          <div v-if="tasks.total === 0" class="text-center py-16 text-gray-500">
            <Clock class="w-16 h-16 mx-auto mb-4 text-gray-300" />
            <p class="text-lg">No tasks found</p>
          </div>
        </div>
      </div>
    </div>

    <AddTaskModal :open="showAdd" @close="showAdd = false" />
    <EditTaskModal :open="showEdit" :task="selectedTask" @close="showEdit = false" />
    <ViewTaskModal :open="showView" :task="selectedTask" @close="showView = false" @edit="showEdit = true; showView = false" />
  </AppLayout>
</template>