<script setup lang="ts">
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import DatePicker from '@/components/form/DatePicker.vue'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps<{
  open: boolean
  note?: any
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
  id: null,
  legal_case_id: null,
  date: '',
  title: '',
  note: '',
})

watch(
  () => props.note,
  (n) => {
    if (n && props.open) {
      form.id = n.id
      form.legal_case_id = n.legal_case_id
      form.date = n.date
      form.title = n.title || ''
      form.note = n.note || ''
    }
  },
  { immediate: true }
)

const submit = () => {
  form.put(`/legal-cases/case-notes/${form.id}`, {
    onSuccess: () => {
      emit('success')
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Note</DialogTitle>
        <DialogDescription>Update note: "{{ note?.title }}"</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6 mt-4">
        <div>
          <Label>Title</Label>
          <Input v-model="form.title" class="mt-1" />
        </div>

        <div>
          <Label>Date</Label>
          <DatePicker v-model="form.date" class="mt-1" />
        </div>

        <div>
          <Label>Note</Label>
          <TextArea
            v-model="form.note"
            rows="10"
            class="mt-1 resize-none"
            placeholder="Update your note..."
          />
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Updating...' : 'Update Note' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>