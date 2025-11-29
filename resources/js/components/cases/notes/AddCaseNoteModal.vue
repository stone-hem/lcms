<script setup lang="ts">
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
  legalCaseId: number
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({
  legal_case_id: props.legalCaseId,
  date: new Date().toISOString().split('T')[0], // today
  title: '',
  note: '',
  // attachments handled separately if needed
})

const submit = () => {
  form.post('/legal-cases/case-notes', {
    onSuccess: () => {
      form.reset()
      form.date = new Date().toISOString().split('T')[0]
      emit('success')
      emit('close')
    },
    preserveScroll: true,
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add Case Note</DialogTitle>
        <DialogDescription>
          Record an important update or observation for this case
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6 mt-4">
        <div>
          <Label>Title <span class="text-red-600">*</span></Label>
          <Input
            v-model="form.title"
            required
            placeholder="e.g. Client Meeting Summary"
            class="mt-1"
          />
          <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">
            {{ form.errors.title }}
          </p>
        </div>

        <div>
          <Label>Date</Label>
          <DatePicker v-model="form.date" class="mt-1" />
        </div>

        <div>
          <Label>Note <span class="text-red-600">*</span></Label>
          <TextArea
            v-model="form.note"
            required
            rows="8"
            placeholder="Write your detailed note here..."
            class="mt-1 resize-none"
          />
          <p v-if="form.errors.note" class="text-sm text-red-600 mt-1">
            {{ form.errors.note }}
          </p>
        </div>

        <!-- Optional: Add file upload later if needed -->
        <!-- <div>
          <Label>Attachments (Optional)</Label>
          <div class="border-2 border-dashed rounded-lg p-6 text-center">
            <Paperclip class="h-10 w-10 mx-auto text-muted-foreground mb-3" />
            <p class="text-sm text-muted-foreground">Drag & drop files or click to browse</p>
          </div>
        </div> -->

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Save Note' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>