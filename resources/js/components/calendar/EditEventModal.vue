<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import { FileText, Trash2, Paperclip, X } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  event: any
  presets: any
}>()

const emit = defineEmits(['close', 'success'])

const files = ref<File[]>([])
const existingAttachments = ref<any[]>([])

const form = useForm({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  case_id: '',
  category_id: '',
  priority: 2,
  lawyer_ids: [] as number[],
  _method: 'PUT' // Laravel trick for PUT via POST
})

// Watch for event change and populate form
watch(
  () => props.event,
  (event) => {
    if (!event || !props.open) return

    form.title = event.title || ''
    form.description = event.description || ''
    form.start_date = event.start_date || event.start || ''
    form.end_date = event.end_date || event.end || event.start_date || ''
    form.case_id = event.legal_case_id || event.legal_case?.id || ''
    form.category_id = event.category_id || ''
    form.priority = event.priority || 2
    form.lawyer_ids = event.participants?.map((p: any) => p.lawyer?.id || p.user?.id).filter(Boolean) || []

    // Keep track of existing attachments (so we don't delete them accidentally)
    existingAttachments.value = event.attachments || []
  },
  { immediate: true }
)

const submit = () => {
  const formData = new FormData()

  // Append all form fields
  Object.entries(form.data()).forEach(([key, value]) => {
    if (Array.isArray(value)) {
      value.forEach(v => formData.append(`${key}[]`, String(v)))
    } else if (value !== null && value !== '') {
      formData.append(key, String(value))
    }
  })

  // Append new files
  files.value.forEach((file, i) => {
    formData.append(`new_attachments[${i}]`, file)
  })

  // Optional: send list of attachments to keep (if you allow deletion later)
  existingAttachments.value.forEach(att => {
    formData.append('keep_attachments[]', att.id)
  })

  form.post(`/calendar/events/${props.event.id}`, {
    data: formData,
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      files.value = []
      emit('success')
      emit('close')
    },
    onError: (errors) => {
      console.log('Edit errors:', errors)
    }
  })
}

const removeExistingAttachment = (index: number) => {
  existingAttachments.value.splice(index, 1)
}

const removeNewFile = (index: number) => {
  files.value.splice(index, 1)
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Event / Task / Activity</DialogTitle>
      </DialogHeader>
      
      <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mx-8 mt-6">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 space-y-2">
              <div class="flex items-center gap-2 font-semibold">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>There were errors with your submission</span>
              </div>
              <ul class="ml-7 list-disc space-y-1 text-sm">
                <li v-for="(error, field) in $page.props.errors" :key="field">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>


      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <Label>Title <span class="text-red-600">*</span></Label>
          <Input v-model="form.title" required placeholder="Enter title" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Start Date <span class="text-red-600">*</span></Label>
            <Input v-model="form.start_date" type="date" required />
          </div>
          <div>
            <Label>End Date <span class="text-red-600">*</span></Label>
            <Input v-model="form.end_date" type="date" required />
          </div>
        </div>

        <div>
          <Label>Case</Label>
          <select v-model="form.case_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">No case</option>
            <option v-for="c in presets.legal_cases" :key="c.id" :value="c.id">
              {{ c.case_number }} - {{ c.title }}
            </option>
          </select>
        </div>

        <div>
          <Label>Category</Label>
          <select v-model="form.category_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">Select category</option>
            <option v-for="cat in presets.event_categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div>
          <Label>Priority</Label>
          <select v-model="form.priority" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
            <option :value="1">Low</option>
            <option :value="2">Medium</option>
            <option :value="3">High</option>
          </select>
        </div>

        <div>
          <Label>Lawyers / Participants</Label>
          <select
            v-model="form.lawyer_ids"
            multiple
            class="w-full px-3 py-2 border border-slate-300 rounded-lg h-32 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option v-for="l in presets.lawyers" :key="l.id" :value="l.id">
              {{ l.name }} ({{ l.email }})
            </option>
          </select>
          <p class="text-xs text-slate-500 mt-1">Hold Ctrl/Cmd to select multiple</p>
        </div>

        <div>
          <Label>Description</Label>
          <TextArea v-model="form.description" rows="4" placeholder="Add notes or details..." />
        </div>

        <!-- Existing Attachments -->
        <!-- <div v-if="existingAttachments.length" class="space-y-2">
          <Label>Current Attachments</Label>
          <div v-for="(file, i) in existingAttachments" :key="i" class="flex items-center justify-between p-3 border rounded-lg bg-slate-50">
            <div class="flex items-center gap-3">
              <FileText class="w-5 h-5 text-slate-600" />
              <a :href="`/storage/${file.file_path || file.path}`" target="_blank" class="text-blue-600 hover:underline">
                {{ file.file_name || file.name }}
              </a>
            </div>
            <button
              type="button"
              @click="removeExistingAttachment(i)"
              class="text-red-600 hover:text-red-800"
            >
              <Trash2 class="w-5 h-5" />
            </button>
          </div>
        </div> -->

        <!-- New Attachments -->
        <!-- <div>
          <Label>Add New Attachments</Label>
          <input
            type="file"
            multiple
            @change="e => files = Array.from((e.target as HTMLInputElement).files || [])"
            class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-primary-500 file:text-white hover:file:bg-primary-600"
          />
          <div v-if="files.length" class="mt-3 space-y-2">
            <div v-for="(file, i) in files" :key="i" class="flex items-center justify-between p-3 border rounded-lg bg-green-50">
              <div class="flex items-center gap-3">
                <Paperclip class="w-5 h-5 text-green-600" />
                <span class="text-sm">{{ file.name }} ({{ (file.size / 1024).toFixed(1) }} KB)</span>
              </div>
              <button type="button" @click="removeNewFile(i)" class="text-red-600">
                <X class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div> -->

        <DialogFooter class="border-t pt-4">
          <Button type="button" variant="outline" @click="emit('close')">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700">
            <span v-if="form.processing">Saving...</span>
            <span v-else>Update Event</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>