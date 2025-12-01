<script setup lang="ts">
import { Star, Paperclip, Users, Calendar, AlertCircle, CheckCircle2, Clock, Eye, Edit3, Trash2 } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{ task: any }>()
const emit = defineEmits(['view', 'edit', 'delete', 'favorite'])

const page = usePage()
const userId = page.props.auth.user.id

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

const isFavorite = computed(() =>
  Array.isArray(props.task.faved_by) && props.task.faved_by.includes(userId)
)

// Stop propagation for action buttons
const stopClick = (e: Event) => e.stopPropagation()
</script>

<template>
  <div
    class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-all duration-200 p-5 cursor-pointer group relative"
    @click="emit('view', task)"
  >
    <!-- Action Bar (Top Right on Hover) -->
    <div
      @click.stop
      class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-white rounded-full shadow-md border px-2 py-1 z-10"
    >
      <button @click="emit('view', task)" class="p-1.5 hover:bg-gray-100 rounded" title="View">
        <Eye class="w-4 h-4 text-gray-600" />
      </button>
      <button @click="emit('edit', task)" class="p-1.5 hover:bg-blue-50 rounded" title="Edit">
        <Edit3 class="w-4 h-4 text-blue-600" />
      </button>
      <button @click="emit('delete', task)" class="p-1.5 hover:bg-red-50 rounded" title="Delete">
        <Trash2 class="w-4 h-4 text-red-600" />
      </button>
    </div>

    <!-- Title + Favorite Star -->
    <div class="flex items-start justify-between mb-3">
      <h3 class="font-semibold text-lg pr-10">{{ task.title }}</h3>
   
    </div>

    <!-- Description -->
    <p v-if="task.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
      {{ task.description }}
    </p>

    <!-- Case -->
    <div v-if="task.legal_case" class="text-xs text-blue-600 font-medium mb-3">
      {{ task.legal_case.case_number }} - {{ task.legal_case.title }}
    </div>

    <!-- Footer: Status, Date, Assignees, Attachments + Priority -->
    <div class="flex items-center justify-between text-sm">
      <div class="flex items-center gap-4 text-gray-500">
        <div class="flex items-center gap-1">
          <component :is="statusIcon[task.status]" class="w-4 h-4" />
          <span class="capitalize">{{ ['Open', 'In Progress', 'Completed'][task.status] }}</span>
        </div>

        <div v-if="task.due_date" class="flex items-center gap-1">
          <Calendar class="w-4 h-4" />
          <span>{{ new Date(task.due_date).toLocaleDateString() }}</span>
        </div>

        <div v-if="task.assignees?.length" class="flex items-center gap-1">
          <Users class="w-4 h-4" />
          <span>{{ task.assignees.length }}</span>
        </div>
        

        <div v-if="task.attachments?.length" class="flex items-center gap-1">
          <Paperclip class="w-4 h-4" />
          <span>{{ task.attachments.length }}</span>
          
        </div>
      </div>

      <!-- Priority -->
      <span :class="`text-${priorityColor[task.priority]}-600 font-semibold text-xs uppercase`">
        {{ ['Low', 'Medium', 'High', 'Urgent'][task.priority] }}
      </span>
      <button
        @click.stop="emit('favorite', task)"
        class="text-yellow-500 hover:scale-110 transition-transform"
        :title="isFavorite ? 'Remove from favorites' : 'Add to favorites'"
      >
        <Star :class="{ 'fill-yellow-400 text-yellow-400': isFavorite }" class="w-5 h-5" />
      </button>
    </div>
  </div>
</template>