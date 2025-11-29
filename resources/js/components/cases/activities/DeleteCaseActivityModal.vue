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
  activity?: any
}>()

const emit = defineEmits(['close', 'deleted'])

const form = useForm({})

const confirmDelete = () => {
  form.delete(`/legal_cases/case-activities/${props.activity.id}`, {
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
        <DialogTitle>Delete Activity</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete "<strong>{{ activity?.title }}</strong>"?
          This action cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" @click="confirmDelete" :disabled="form.processing">
          Delete Activity
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>