<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { watch, ref } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Select from '@/components/form/Select.vue'
import MultiSelect from '@/components/form/MultiSelect.vue'
import DatePicker from '@/components/form/DatePicker.vue'
import { Upload, X } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  task: any
  cases?: any[]
  users?: any[]
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
  title: '',
  description: '',
  legal_case_id: null as number | null,
  assignee_ids: [] as number[],
  due_date: '' as string,
  priority: '1',
  status: '0',
  attachments: [] as File[],
})

const selectedFiles = ref<File[]>([])

watch(
  () => props.task,
  (task) => {
    if (task) {
      form.title = task.title
      form.description = task.description || ''
      form.legal_case_id = task.legal_case_id
      form.assignee_ids = task.assignees?.map((a: any) => a.id) || []
      form.due_date = task.due_date || ''
      form.priority = task.priority?.toString() || '1'
      form.status = task.status?.toString() || '0'
    }
  },
  { immediate: true }
)

const submit = () => {
  const formData = new FormData()
  formData.append('_method', 'PUT')
  formData.append('title', form.title)
  formData.append('description', form.description || '')
  if (form.legal_case_id) formData.append('legal_case_id', form.legal_case_id.toString())
  if (form.due_date) formData.append('due_date', form.due_date)
  formData.append('priority', form.priority)
  formData.append('status', form.status)
  form.assignee_ids.forEach(id => formData.append('assignee_ids[]', id.toString()))
  selectedFiles.value.forEach(file => formData.append('attachments[]', file))

  form.post(route('tasks.update', props.task.id), {
    data: formData,
    forceFormData: true,
    onSuccess: () => {
      selectedFiles.value = []
      emit('success')
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Task</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div class="col-span-2">
            <Label>Title <span class="text-red-600">*</span></Label>
            <Input v-model="form.title" required />
          </div>

          <div class="col-span-2">
            <Label>Description</Label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            />
          </div>

          <div>
            <Label>Related Case</Label>
            <Select v-model="form.legal_case_id">
              <option value="">No case</option>
              <option v-for="c in props.cases" :key="c.id" :value="c.id">
                {{ c.case_number }} - {{ c.title }}
              </option>
            </Select>
          </div>

          <div>
            <Label>Priority</Label>
            <Select v-model="form.priority">
              <option value="0">Low</option>
              <option value="1">Medium</option>
              <option value="2">High</option>
              <option value="3">Urgent</option>
            </Select>
          </div>

          <div>
            <Label>Status</Label>
            <Select v-model="form.status">
              <option value="0">Open</option>
              <option value="1">In Progress</option>
              <option value="2">Completed</option>
            </Select>
          </div>

          <div>
            <Label>Due Date</Label>
            <DatePicker v-model="form.due_date" placeholder="Select due date" />
          </div>

          <div class="col-span-2">
            <Label>Assign To</Label>
            <MultiSelect
              v-model="form.assignee_ids"
              :options="props.users || []"
              placeholder="Search lawyers..."
              display-key="first_name"
              value-key="id"
              returnIdsOnly
            />
          </div>

          <div class="col-span-2">
            <Label>Attachments (New)</Label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
              <Upload class="mx-auto h-12 w-12 text-gray-400 mb-3" />
              <input
                type="file"
                multiple
                class="hidden"
                id="edit-task-files"
                @change="(e: any) => selectedFiles = Array.from(e.target.files)"
              />
              <label for="edit-task-files" class="cursor-pointer text-sm font-medium text-blue-600">
                Click to add more files
              </label>
              <p class="text-xs text-gray-500 mt-2">
                {{ selectedFiles.length ? `${selectedFiles.length} new file(s)` : 'No new files' }}
              </p>
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Update Task' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>