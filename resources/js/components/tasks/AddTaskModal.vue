<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Select from '@/components/form/Select.vue'
import MultiSelect from '@/components/form/MultiSelect.vue'
import DatePicker from '@/components/form/DatePicker.vue'
import { Upload, X, UserPlus } from 'lucide-vue-next'
import TextArea from '@/components/form/TextArea.vue'
import { displayErrors } from '@/lib/errors'

const props = defineProps<{
  open: boolean
  cases?: any[]
  users?: any[],
  lawyers?: any[]
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
  title: '',
  description: '',
  legal_case_id: null as number | null,
  assignee_ids: [] as number[],
  end_datetime: '' as string,
  priority: '1',
  status: '0',
  attachments: [] as File[],
})

const selectedFiles = ref<File[]>([])

const submit = () => {
  const formData = new FormData()

  formData.append('title', form.title)
  formData.append('description', form.description || '')
  if (form.legal_case_id) formData.append('legal_case_id', form.legal_case_id.toString())
  if (form.end_datetime) formData.append('end_datetime', form.end_datetime)
  formData.append('priority', form.priority)
  formData.append('status', form.status)

  form.assignee_ids.forEach(id => formData.append('assignee_ids[]', id.toString()))
  selectedFiles.value.forEach(file => formData.append('attachments[]', file))

  form.post('/tasks', {
    data: formData,
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      selectedFiles.value = []
      emit('success')
      emit('close')
    },
    onError: (error:any) => {
        displayErrors(error)
    }
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Create New Task</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <!-- Title -->
          <div class="col-span-2">
            <Label>Title <span class="text-red-600">*</span></Label>
            <Input v-model="form.title" required placeholder="Enter task title" />
          </div>

          <!-- Description -->
          <div class="col-span-2">
            <Label>Description</Label>
            <TextArea
              v-model="form.description"
              rows="4"
              placeholder="Task details..."
              required
            />
          </div>

          <!-- Related Case -->
          <div>
            <Label>Related Case</Label>
            <Select v-model="form.legal_case_id" placeholder="Select case (optional)">
              <option value="">No case</option>
              <option v-for="c in props.cases" :key="c.id" :value="c.id">
                {{ c.case_number }} - {{ c.title }}
              </option>
            </Select>
          </div>

          <!-- Priority -->
          <div>
            <Label>Priority</Label>
            <Select v-model="form.priority">
              <option value="0">Low</option>
              <option value="1" selected>Medium</option>
              <option value="2">High</option>
              <option value="3">Urgent</option>
            </Select>
          </div>

          <!-- Status -->
          <div>
            <Label>Status</Label>
            <Select v-model="form.status">
              <option value="0" selected>Open</option>
              <option value="1">In Progress</option>
              <option value="2">Completed</option>
            </Select>
          </div>

          <!-- Due Date -->
          <div>
            <Label>Due Date</Label>
            <DatePicker v-model="form.end_datetime" placeholder="Select due date" :required="true"/>
          </div>

          <!-- Assignees -->
          <div class="col-span-2">
            <Label>Assign To</Label>
            <MultiSelect
              v-model="form.assignee_ids"
              :options="lawyers || []"
              placeholder="Search and select lawyers..."
              display-key="first_name"
              value-key="id"
              returnIdsOnly
            />
          </div>

          <!-- Attachments -->
          <div class="col-span-2">
            <Label>Attachments</Label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
              <Upload class="mx-auto h-12 w-12 text-gray-400 mb-3" />
              <input
                type="file"
                multiple
                class="hidden"
                id="add-task-files"
                @change="(e: any) => selectedFiles = Array.from(e.target.files)"
              />
              <label
                for="add-task-files"
                class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-700"
              >
                Click to upload files
              </label>
              <p class="text-xs text-gray-500 mt-2">
                {{ selectedFiles.length ? `${selectedFiles.length} file(s) selected` : 'No files chosen' }}
              </p>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create Task' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
