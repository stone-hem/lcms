<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
  RefreshCw, 
  Plus, 
  Calendar,
  GripVertical 
} from 'lucide-vue-next'
import draggable from 'vuedraggable'

// Dialog components
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Props from Controller
const props = defineProps<{
  content: Array<{
    id: number
    name: string
    color: string
    tasks: Array<any>
  }>
}>()

// Local state (no Pinia!)
const columns = ref<any[]>([])
const loading = ref(false)

// Modal states
const showAddTask = ref(false)
const showEditTask = ref(false)
const selectedTask = ref<any>(null)
const selectedColumn = ref<any>(null)

// Add Task Modal Form
const addForm = ref({
  title: '',
  description: '',
  start_datetime: '',
  end_datetime: '',
  legal_case_id: '',
  assignee_ids: [] as number[],
  status: 1
})

onMounted(() => {
  columns.value = [...props.content]
})

// Open Add Task Modal
const openAddTask = (column: any) => {
  selectedColumn.value = column
  addForm.value.status = column.id
  showAddTask.value = true
}

// Open Edit Task Modal
const openEditTask = (task: any) => {
  selectedTask.value = task
  showEditTask.value = true
}

// Drag & Drop Handler
const onDragEnd = (evt: any) => {
  const { oldIndex, newIndex, from, to } = evt
  if (!from || !to) return

  const fromColumn = columns.value.find(col => col.tasks === from.__draggable_context?.list)
  const toColumn = columns.value.find(col => col.tasks === to.__draggable_context?.list)

  if (!fromColumn || !toColumn || fromColumn.id === toColumn.id) return

  const movedTask = fromColumn.tasks[oldIndex]
  const payload = [{
    task_id: movedTask.id,
    new_status: toColumn.id
  }]

  // Optimistically update UI
  fromColumn.tasks.splice(oldIndex, 1)
  toColumn.tasks.splice(newIndex, 0, movedTask)

  // Send to server
  router.post(route('tasks.update-status'), { updates: payload }, {
    preserveState: true,
    preserveScroll: true,
    onError: () => {
      // Revert on error
      router.visit(route('kanban.index'))
    }
  })
}

// Refresh page
const refresh = () => {
  router.visit(route('kanban.index'), { preserveState: true })
}
</script>

<template>
  <AppLayout>
    <Head title="Kanban Board" />

    <!-- Loading Spinner -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div class="min-h-screen bg-slate-50 dark:bg-black md:p-8 p-4">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Task Board</h1>
        <Button text="Refresh" btnClass="btn-outline-primary" @click="refresh">
          <RefreshCw class="w-5 h-5" />
        </Button>
      </div>

      <!-- Kanban Board -->
      <div class="flex gap-6 overflow-x-auto pb-6">
        <draggable
          :list="columns"
          group="columns"
          item-key="id"
          class="flex gap-6"
          handle=".column-handle"
        >
          <template #item="{ element: column }">
            <div class="w-80 flex-none">
              <!-- Column Header -->
              <div class="bg-white dark:bg-slate-800 rounded-t-lg shadow-sm px-5 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-8 rounded" :style="{ backgroundColor: column.color }"></div>
                  <h3 class="font-semibold text-slate-800 dark:text-white">
                    {{ column.name }} <span class="text-slate-500">({{ column.tasks.length }})</span>
                  </h3>
                </div>
                <button
                  @click="openAddTask(column)"
                  class="w-8 h-8 rounded hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center"
                >
                  <Plus class="w-5 h-5" />
                </button>
              </div>

              <!-- Tasks -->
              <div class="bg-slate-100 dark:bg-slate-700 min-h-96 rounded-b-lg p-3 space-y-3">
                <draggable
                  :list="column.tasks"
                  :group="{ name: 'tasks', pull: 'clone', put: false }"
                  :animation="200"
                  ghost-class="opacity-50"
                  :item-key="(task: any) => task.id"
                  @end="onDragEnd"
                >
                  <template #item="{ element: task }">
                    <Card
                      class="cursor-move p-4 hover:shadow-md transition-shadow"
                      @click="openEditTask(task)"
                    >
                      <div class="flex items-start justify-between mb-3">
                        <h4 class="font-medium text-slate-800 dark:text-white truncate">
                          {{ task.title }}
                        </h4>
                      </div>

                      <p v-if="task.description" class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2 mb-3">
                        {{ task.description }}
                      </p>

                      <div v-if="task.legal_case" class="text-xs text-primary-600 font-medium mb-3">
                        {{ task.legal_case.case_number }}
                      </div>

                      <div class="flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2">
                          <Calendar class="w-4 h-4" />
                          <span>{{ new Date(task.start_datetime).toLocaleDateString() }}</span>
                        </div>
                        <div v-if="task.assignees?.length" class="flex -space-x-2">
                          <div
                            v-for="assignee in task.assignees.slice(0, 3)"
                            :key="assignee.id"
                            class="w-7 h-7 rounded-full bg-slate-300 border-2 border-white flex items-center justify-center text-xs font-medium"
                          >
                            {{ assignee.user?.name?.charAt(0) || '?' }}
                          </div>
                          <div v-if="task.assignees.length > 3" class="w-7 h-7 rounded-full bg-slate-400 border-2 border-white flex items-center justify-center text-xs">
                            +{{ task.assignees.length - 3 }}
                          </div>
                        </div>
                      </div>
                    </Card>
                  </template>
                </draggable>

                <!-- Empty State -->
                <div v-if="!column.tasks.length" class="text-center text-slate-500 py-8 text-sm">
                  No tasks
                </div>
              </div>
            </div>
          </template>
        </draggable>

        <!-- Add New Column Placeholder -->
        <div class="w-80 flex-none">
          <div class="bg-slate-100 dark:bg-slate-700 rounded-lg h-full min-h-96 flex items-center justify-center">
            <Button text="Add Column" btnClass="btn-outline">
              <Plus class="w-5 h-5 mr-2" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Task Modal -->
    <Dialog :open="showAddTask" @update:open="showAddTask = false">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Add New Task to "{{ selectedColumn?.name }}"</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="() => {
          router.post(route('tasks.store'), addForm, {
            onSuccess: () => {
              showAddTask = false
              refresh()
            }
          })
        }" class="space-y-4">
          <div>
            <Label>Title *</Label>
            <Input v-model="addForm.title" required />
          </div>
          <div>
            <Label>Description</Label>
            <textarea v-model="addForm.description" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Start Date</Label>
              <Input v-model="addForm.start_datetime" type="date" />
            </div>
            <div>
              <Label>Due Date</Label>
              <Input v-model="addForm.end_datetime" type="date" />
            </div>
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showAddTask = false">Cancel</Button>
            <Button type="submit">Create Task</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Task Modal (Simple View + Edit) -->
    <Dialog :open="showEditTask" @update:open="showEditTask = false">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>{{ selectedTask?.title }}</DialogTitle>
        </DialogHeader>
        <div class="space-y-4">
          <p><strong>Description:</strong> {{ selectedTask?.description || 'None' }}</p>
          <p><strong>Case:</strong> {{ selectedTask?.legal_case?.case_number || 'None' }}</p>
          <p><strong>Status:</strong> {{ selectedTask?.status_name || selectedTask?.status }}</p>
          <div>
            <strong>Assignees:</strong>
            <div class="flex gap-2 mt-2">
              <span v-for="a in selectedTask?.assignees" :key="a.id" class="px-3 py-1 bg-slate-200 rounded-full text-sm">
                {{ a.user?.name }}
              </span>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showEditTask = false">Close</Button>
          <Button @click="router.visit(route('tasks.edit', selectedTask.id))">Edit Task</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.ghost-card {
  opacity: 0.5;
  background: #f0f0f0;
  border: 2px dashed #4669fa;
}
</style>