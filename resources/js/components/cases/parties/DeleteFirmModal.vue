<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps<{ open: boolean; firm?: any }>()
const emit = defineEmits(['close', 'deleted'])
const form = useForm({})

const confirm = () => {
  form.delete('/parties/firm', {
    data: { id: props.firm.id },
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
        <DialogTitle>Delete Firm</DialogTitle>
        <DialogDescription>
          Permanently delete <strong>{{ firm?.party.name }}</strong>?
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" @click="confirm">Delete Firm</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>