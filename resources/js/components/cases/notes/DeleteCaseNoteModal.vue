<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  open: boolean
  note?: any
}>()

const emit = defineEmits(['close', 'deleted'])

const form = useForm({})

const confirmDelete = () => {
  form.delete(`/legal-cases/case-notes/${props.note.id}`, {
    onSuccess: () => {
      emit('deleted')
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Delete Note</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete the note "<strong>{{ note?.title }}</strong>"?
          <br />
          This action cannot be undone.
        </DialogDescription>
      </DialogHeader>

      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" @click="confirmDelete" :disabled="form.processing">
          Delete Note
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>