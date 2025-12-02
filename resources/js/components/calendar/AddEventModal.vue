<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import MultiSelect from '@/components/form/MultiSelect.vue' // Import your multiselect

const props = defineProps<{
  open: boolean
  presets: any
  initialDate?: string
}>()

const emit = defineEmits(['close', 'success'])

const files = ref<File[]>([])

// Format lawyers for multiselect
const lawyerOptions = computed(() => {
  return props.presets.lawyers.map((lawyer: any) => ({
    id: lawyer.id,
    name: lawyer.name || `${lawyer.first_name} ${lawyer.last_name}`
  }))
})

const form = useForm({
  name: '',
  description: '',
  start_date: props.initialDate || '',
  start_time: '09:00', // Added time fields
  end_date: props.initialDate || '',
  end_time: '10:00', // Added time fields
  case_id: '',
  category_id: '',
  priority: 1,
  lawyers: [] as number[], // Change to array for multiselect
})

// Handle lawyer selection from multiselect
const handleLawyersChange = (selectedLawyerIds: number[]) => {
  form.lawyers = selectedLawyerIds
}

// Handle file selection
const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    files.value = Array.from(target.files)
  }
}

const submit = () => {
  // Format datetime strings
  const startDateTime = `${form.start_date} ${form.start_time}:00`
  const endDateTime = `${form.end_date} ${form.end_time}:00`

  const formData = new FormData()
  
  // Add basic fields
  formData.append('name', form.name)
  formData.append('description', form.description)
  formData.append('start_date', startDateTime)
  formData.append('end_date', endDateTime)
  formData.append('case_id', form.case_id)
  formData.append('category', form.category_id)
  formData.append('priority', form.priority)
  
  // Add lawyers as array
  form.lawyers.forEach(lawyerId => {
    formData.append('lawyers[]', lawyerId.toString())
  })
  
  // Add files
  files.value.forEach((file, index) => {
    formData.append('upload_files[]', file)
  })

  form.post('/calender/store/event', {
    data: formData,
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      files.value = []
      emit('success')
    },
    onError: (errors) => {
      console.error('Error creating event:', errors)
    }
  })
}

// Reset form when dialog closes
const handleClose = () => {
  form.reset()
  files.value = []
  emit('close')
}
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add New Event</DialogTitle>
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
          <Input v-model="form.name" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Start Date <span class="text-red-600">*</span></Label>
            <Input v-model="form.start_date" type="date" required />
          </div>
          <div>
            <Label>Start Time <span class="text-red-600">*</span></Label>
            <Input v-model="form.start_time" type="time" required />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>End Date <span class="text-red-600">*</span></Label>
            <Input v-model="form.end_date" type="date" required />
          </div>
          <div>
            <Label>End Time <span class="text-red-600">*</span></Label>
            <Input v-model="form.end_time" type="time" required />
          </div>
        </div>

        <div>
          <Label>Case</Label>
          <select v-model="form.case_id" class="w-full px-3 py-2 border rounded-lg">
            <option value="">No case</option>
            <option v-for="c in presets.legal_cases" :key="c.id" :value="c.id">
              {{ c.case_number }} - {{ c.title }}
            </option>
          </select>
        </div>

        <div>
          <Label>Category</Label>
          <select v-model="form.category_id" class="w-full px-3 py-2 border rounded-lg">
            <option value="">Select</option>
            <option v-for="cat in presets.event_categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div>
          <Label>Priority</Label>
          <select v-model="form.priority" class="w-full px-3 py-2 border rounded-lg">
            <option value="1">Low</option>
            <option value="2">Medium</option>
            <option value="3">High</option>
          </select>
        </div>

        <div>
          <Label>Lawyers</Label>
          <MultiSelect
            :options="lawyerOptions"
            v-model="form.lawyers"
            :return-ids-only="true"
            label="Select Lawyers"
            placeholder="Select lawyers..."
            @change="handleLawyersChange"
          />
        </div>

        <div>
          <Label>Description</Label>
          <TextArea v-model="form.description" rows="4" />
        </div>
<!-- 
        <div>
          <Label>Attachments</Label>
          <div class="space-y-2">
            <input
              type="file"
              multiple
              @change="handleFileChange"
              class="w-full px-3 py-2 border rounded-lg bg-white"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
            />
            <div v-if="files.length" class="mt-2 space-y-1">
              <div class="text-sm text-slate-600">
                {{ files.length }} file(s) selected:
              </div>
              <ul class="text-xs text-slate-500 space-y-1">
                <li v-for="(file, index) in files" :key="index">
                  {{ file.name }} ({{ (file.size / 1024).toFixed(2) }} KB)
                </li>
              </ul>
            </div>
          </div>
        </div> -->

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
          <Button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700">
            {{ form.processing ? 'Saving...' : 'Create Event' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>