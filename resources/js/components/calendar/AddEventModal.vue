<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'

const props = defineProps<{
  open: boolean
  presets: any
  initialDate?: string
}>()

const emit = defineEmits(['close', 'success'])

const files = ref<File[]>([])

const form = useForm({
  title: '',
  description: '',
  start_date: props.initialDate || '',
  end_date: props.initialDate || '',
  case_id: '',
  category_id: '',
  priority: 2,
  lawyer_ids: [] as number[],
})

const submit = () => {
  const formData = new FormData()
  Object.entries(form.data()).forEach(([key, value]) => {
    if (Array.isArray(value)) {
      value.forEach(v => formData.append(`${key}[]`, v))
    } else {
      formData.append(key, value as string)
    }
  })
  files.value.forEach((file, i) => formData.append(`attachments[${i}]`, file))

  form.post('/calendar/events', {
    data: formData,
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      files.value = []
      emit('success')
    }
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add New Event</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <Label>Title <span class="text-red-600">*</span></Label>
          <Input v-model="form.title" required />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Start Date</Label>
            <Input v-model="form.start_date" type="date" required />
          </div>
          <div>
            <Label>End Date</Label>
            <Input v-model="form.end_date" type="date" required />
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
          <Label>Lawyers</Label>
          <select v-model="form.lawyer_ids" multiple class="w-full px-3 py-2 border rounded-lg h-32">
            <option v-for="l in presets.lawyers" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </div>

        <div>
          <Label>Description</Label>
          <TextArea v-model="form.description" rows="4" />
        </div>

        <div>
          <Label>Attachments</Label>
          <input
            type="file"
            multiple
            @change="e => files = Array.from(e.target.files || [])"
            class="w-full px-3 py-2 border rounded-lg bg-white"
          />
          <div v-if="files.length" class="mt-2 text-sm text-slate-600">
            {{ files.length }} file(s) selected
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Create' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>