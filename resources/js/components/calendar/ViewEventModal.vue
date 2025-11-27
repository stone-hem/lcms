<script setup lang="ts">
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  item: any
}>()

const emit = defineEmits(['close', 'edit'])

const getType = () => {
  if (!props.item) return ''
  if (props.item.color === 'teal') return 'Task'
  if (props.item.color === 'green') return 'Activity'
  return 'Event'
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle>{{ item?.title || 'Event Details' }}</DialogTitle>
        <div class="text-sm text-slate-500">{{ getType() }}</div>
      </DialogHeader>

      <div class="space-y-4 text-sm">
        <div>
          <strong>Date:</strong>
          {{ item?.start_date || item?.start }} → {{ item?.end_date || item?.end || 'Same day' }}
        </div>

        <div v-if="item?.legal_case">
          <strong>Case:</strong> {{ item.legal_case.case_number }} - {{ item.legal_case.title }}
        </div>

        <div v-if="item?.description">
          <strong>Description:</strong>
          <p class="mt-1 text-slate-700">{{ item.description }}</p>
        </div>

        <div v-if="item?.participants?.length">
          <strong>Participants:</strong>
          <div class="flex flex-wrap gap-2 mt-2">
            <span v-for="p in item.participants" :key="p.id" class="px-3 py-1 bg-slate-100 rounded-full text-xs">
              {{ p.lawyer?.name || p.user?.name }}
            </span>
          </div>
        </div>

        <div v-if="item?.attachments?.length" class="mt-4">
          <strong>Attachments:</strong>
          <div class="space-y-2 mt-2">
            <a
              v-for="file in item.attachments"
              :key="file.id"
              :href="`/storage/${file.file_path}`"
              target="_blank"
              class="flex items-center gap-3 p-2 border rounded hover:bg-slate-50"
            >
              <FileText class="w-5 h-5 text-slate-500" />
              <span class="text-blue-600 underline">{{ file.file_name }}</span>
            </a>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Close</Button>
        <Button @click="emit('edit')">Edit</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>