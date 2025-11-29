<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  open: boolean
  party?: any
}>()
const emit = defineEmits(['close', 'deleted'])

const form = useForm({
  id: props.party?.id
})

const confirmDelete = () => {
  form.post('/parties/individual', {
    data: { id: props.party.id },
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
        <DialogTitle>Delete Individual</DialogTitle>
        <DialogDescription>
          Permanently delete <strong>{{ party.party.first_name }} {{ party.party.last_name }}</strong>?
          This cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" @click="confirmDelete" :disabled="form.processing">
          Delete
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>