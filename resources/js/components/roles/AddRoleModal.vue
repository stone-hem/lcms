<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'


const props = defineProps<{ open: boolean }>()
const emit = defineEmits(['close'])

const form = useForm({
  name: '',
  description: '',
  active: true,
})

const submit = () => {
  form.post(route('roles.store'), {
    onSuccess: () => {
      form.reset()
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Add New Role</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.name" required placeholder="e.g. Internal Counsel" />
        </div>
        <div>
          <Label>Description</Label>
          <Input v-model="form.description" placeholder="Optional description" />
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" v-model="form.active" />
          <Label>Active</Label>
        </div>
        <DialogFooter>
          <Button type="button" variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Create Role</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>