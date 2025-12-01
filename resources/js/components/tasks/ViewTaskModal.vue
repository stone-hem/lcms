<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
  Eye, Download, Calendar, Users, Paperclip, AlertCircle,
  Clock, CheckCircle2, Edit3, Star, Loader2
} from 'lucide-vue-next'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import Select from '@/components/form/Select.vue'

const props = defineProps<{ open: boolean; task: any }>()
const emit = defineEmits(['close', 'edit', 'updated'])

const page = usePage()
const userId = page.props.auth?.user?.id

const isLoading = ref(false)

const isFavorite = computed(() =>
  Array.isArray(props.task?.faved_by) && props.task.faved_by.includes(userId)
)

const priorityBadge = [
  'bg-gray-100 text-gray-800',
  'bg-green-100 text-green-800',
  'bg-yellow-100 text-yellow-800',
  'bg-red-100 text-red-800'
]
const priorityLabels = ['Low', 'Medium', 'High', 'Urgent']

const statusConfig = [
  { value: 0, label: 'Open', icon: AlertCircle, color: 'text-blue-600' },
  { value: 1, label: 'In Progress', icon: Clock, color: 'text-yellow-600' },
  { value: 2, label: 'Completed', icon: CheckCircle2, color: 'text-green-600' },
]

const toggleFavorite = () => {
  if (isLoading.value) return
  isLoading.value = true

  router.post(
    `/tasks/${props.task.id}/favourite`,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        isLoading.value = false
        emit('updated') // Trigger parent refresh (list/kanban)
      },
    }
  )
}

const changeStatus = (newStatus: string | number) => {
  const status = Number(newStatus)
  if (isLoading.value || status === props.task.status) return

  isLoading.value = true

  router.patch(
    `/tasks/${props.task.id}/status/${status}`,
    {},
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => {
        isLoading.value = false
        emit('updated')
      },
    }
  )
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="text-2xl pr-32">
          {{ task?.title }}
        </DialogTitle>

        <!-- Action Buttons -->
        <div class="absolute right-6 top-6 flex items-center gap-2">
          <!-- Favorite Button -->
          <Button
            variant="ghost"
            size="sm"
            @click="toggleFavorite"
            :disabled="isLoading"
            class="relative"
          >
            <Star
              :class="isFavorite ? 'fill-yellow-400 text-yellow-400' : 'text-gray-400'"
              class="w-5 h-5 transition-all"
            />
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin absolute inset-0 m-auto" />
          </Button>
        </div>
      </DialogHeader>

      <div class="space-y-6 mt-6">
        <!-- Description -->
        <div v-if="task?.description" class="prose max-w-none text-gray-700">
          <div v-html="task.description"></div>
        </div>

        <!-- Status & Priority -->
        <div class="flex flex-wrap items-center gap-8">
          <!-- Status Dropdown -->
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-600">Status:</span>
            <Select
              :model-value="task?.status?.toString()"
              @update:model-value="changeStatus"
              class="w-48"
            >
              <option
                v-for="status in statusConfig"
                :key="status.value"
                :value="status.value"
                :selected="status.value === task?.status"
              >
                <span :class="status.color" class="flex items-center gap-2">
                  <component :is="status.icon" class="w-4 h-4" />
                  {{ status.label }}
                </span>
              </option>
            </Select>
          </div>

          <!-- Priority Badge -->
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-600">Priority:</span>
            <span
              :class="priorityBadge[task?.priority ?? 1]"
              class="px-3 py-1.5 rounded-full text-xs font-medium"
            >
              {{ priorityLabels[task?.priority ?? 1] }}
            </span>
          </div>
        </div>

        <!-- Case & Due Date -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600">
          <div>
            <strong class="text-gray-800">Related Case:</strong><br>
            {{ task?.legal_case
              ? `${task.legal_case.case_number} - ${task.legal_case.title}`
              : 'No case linked'
            }}
          </div>
          <div>
            <strong class="text-gray-800">Due Date:</strong><br>
            {{ task?.due_date
              ? new Date(task.due_date).toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric'
                })
              : 'No due date'
            }}
          </div>
        </div>

        <!-- Assignees -->
        <div v-if="task?.assignees?.length" class="space-y-2">
          <strong class="text-gray-800">Assigned To:</strong>
          <div class="flex flex-wrap gap-2 mt-2">
            <span
              v-for="assignee in task.assignees"
              :key="assignee.id"
              class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-blue-100 text-blue-800 font-medium"
            >
              {{ assignee.first_name }} {{ assignee.last_name }}
            </span>
          </div>
        </div>

        <!-- Attachments -->
        <div v-if="task?.attachments?.length" class="space-y-3">
          <strong class="text-gray-800">Attachments:</strong>
          <div class="space-y-2">
            <a
              v-for="file in task.attachments"
              :key="file.id"
              :href="`/storage/${file.path || file.file_path}`"
              target="_blank"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border"
            >
              <div class="flex items-center gap-3">
                <Paperclip class="w-5 h-5 text-gray-600" />
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ file.file_name }}</p>
                  <p class="text-xs text-gray-500">
                    {{ file.size ? (file.size / 1024).toFixed(1) + ' KB' : '—' }}
                  </p>
                </div>
              </div>
              <div class="flex gap-3">
                <Eye class="w-5 h-5 text-gray-500 hover:text-blue-600 cursor-pointer" />
                <Download class="w-5 h-5 text-gray-500 hover:text-green-600 cursor-pointer" />
              </div>
            </a>
          </div>
        </div>
      </div>

      <DialogFooter class="mt-8">
        <Button variant="outline" @click="emit('close')">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>