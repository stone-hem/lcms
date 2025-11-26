<!-- resources/js/components/tasks/ViewTaskModal.vue -->
<script setup lang="ts">
import { Eye, Download, Calendar, Users, Paperclip, AlertCircle, Clock, CheckCircle2, Edit3 } from 'lucide-vue-next'

const props = defineProps<{ open: boolean; task: any }>()
const emit = defineEmits(['close', 'edit'])

const priorityBadge = ['bg-gray-100 text-gray-800', 'bg-green-100 text-green-800', 'bg-yellow-100 text-yellow-800', 'bg-red-100 text-red-800']
const statusConfig = [
  { icon: AlertCircle, label: 'Open', color: 'text-blue-600' },
  { icon: Clock, label: 'In Progress', color: 'text-yellow-600' },
  { icon: CheckCircle2, label: 'Completed', color: 'text-green-600' },
]
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl">
      <DialogHeader>
        <DialogTitle class="text-2xl">{{ task?.title }}</DialogTitle>
        <Button @click="emit('edit')" class="absolute right-20 top-6">
          <Edit3 class="w-4 h-4 mr-2" /> Edit
        </Button>
      </DialogHeader>

      <div class="space-y-6 mt-6">
        <div v-if="task?.description" class="prose max-w-none" v-html="task.description"></div>

        <div class="grid grid-cols-2 gap-6 text-sm">
          <div>
            <strong>Case:</strong>
            {{ task?.legal_case ? `${task.legal_case.case_number} - ${task.legal_case.title}` : 'No case linked' }}
          </div>
          <div>
            <strong>Status:</strong>
            <span :class="statusConfig[task?.status]?.color" class="flex items-center gap-2">
              <component :is="statusConfig[task?.status]?.icon" class="w-4 h-4" />
              {{ statusConfig[task?.status]?.label }}
            </span>
          </div>
          <div>
            <strong>Priority:</strong>
            <span :class="priorityBadge[task?.priority || 1]" class="px-3 py-1 rounded-full text-xs font-medium">
              {{ ['Low', 'Medium', 'High', 'Urgent'][task?.priority || 1] }}
            </span>
          </div>
          <div>
            <strong>Due Date:</strong>
            {{ task?.due_date ? (new Date(task.due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })) : 'No due date' }}
          </div>
        </div>

        <div v-if="task?.assignees?.length" class="space-y-2">
          <strong>Assigned To:</strong>
          <div class="flex flex-wrap gap-2">
            <span v-for="assignee in task.assignees" :key="assignee.id"
              class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
              {{ assignee.first_name }} {{ assignee.last_name }}
            </span>
          </div>
        </div>

        <div v-if="task?.attachments?.length" class="space-y-3">
          <strong>Attachments:</strong>
          <div class="space-y-2">
            <a
              v-for="file in task.attachments"
              :key="file.id"
              :href="`/storage/${file.file_path}`"
              target="_blank"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100"
            >
              <div class="flex items-center gap-3">
                <Paperclip class="w-4 h-4 text-gray-600" />
                <span class="text-sm">{{ file.file_name }}</span>
              </div>
              <div class="flex gap-2">
                <Eye class="w-4 h-4 text-gray-500 hover:text-blue-600" />
                <Download class="w-4 h-4 text-gray-500 hover:text-green-600" />
              </div>
            </a>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>