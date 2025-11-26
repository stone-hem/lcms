<script setup lang="ts">
import { Star, Paperclip, Users, Calendar, AlertCircle, CheckCircle2, Clock } from 'lucide-vue-next'

const props = defineProps<{ task: any }>()

const priorityColor = {
  0: 'gray',
  1: 'green',
  2: 'yellow',
  3: 'red'
}

const statusIcon = {
  0: AlertCircle,
  1: Clock,
  2: CheckCircle2
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition p-5 cursor-pointer">
    <div class="flex items-start justify-between mb-3">
      <h3 class="font-semibold text-lg">{{ task.title }}</h3>
      <button class="text-yellow-500">
        <Star :class="{ 'fill-current': task.faved_by?.includes($page.props.auth.user.id) }" class="w-5 h-5" />
      </button>
    </div>

    <p v-if="task.description" class="text-gray-600 text-sm mb-4 line-clamp-2">{{ task.description }}</p>

    <div v-if="task.legal_case" class="text-xs text-blue-600 mb-3">
      {{ task.legal_case.case_number }} - {{ task.legal_case.title }}
    </div>

    <div class="flex items-center justify-between text-sm">
      <div class="flex items-center space-x-4 text-gray-500">
        <div class="flex items-center space-x-1">
          <component :is="statusIcon[task.status]" class="w-4 h-4" />
          <span>{{ ['Open', 'In Progress', 'Completed'][task.status] }}</span>
        </div>

        <div v-if="task.due_date" class="flex items-center space-x-1">
          <Calendar class="w-4 h-4" />
          <span>{{ new Date(task.due_date).toLocaleDateString() }}</span>
        </div>

        <div v-if="task.assignees?.length" class="flex items-center space-x-1">
          <Users class="w-4 h-4" />
          <span>{{ task.assignees.length }}</span>
        </div>

        <div v-if="task.attachments?.length" class="flex items-center space-x-1">
          <Paperclip class="w-4 h-4" />
          <span>{{ task.attachments.length }}</span>
        </div>
      </div>

      <span :class="`text-${priorityColor[task.priority]}-600 font-medium`">
        {{ ['Low', 'Medium', 'High', 'Urgent'][task.priority] }}
      </span>
    </div>
  </div>
</template>