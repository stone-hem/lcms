<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {  watch } from 'vue'


const props = defineProps<{ open: boolean; role: any }>()
const emit = defineEmits(['close'])

const form = useForm({
  name: props.role?.name || '',
  description: props.role?.description || '',
  active: !props.role?.deleted_at,
})

watch(() => props.role, (role) => {
  if (role) {
    form.name = role.name
    form.description = role.description || ''
    form.active = !role.deleted_at
  }
})

const submit = () => {
  form.put(route('roles.update', props.role.id), {
    onSuccess: () => emit('close'),
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent>
      <DialogHeader><DialogTitle>Edit Role</DialogTitle></DialogHeader>
      <form @submit.prevent="submit" class="space-y-4">
        <div><Label>Name</Label><Input v-model="form.name" required /></div>
        <div><Label>Description</Label><Input v-model="form.description" /></div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" v-model="form.active" />
          <Label>Active</Label>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit">Update Role</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>