<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import {  watch ,ref} from 'vue'
import { Label } from '@/components/ui/label'



const props = defineProps<{ open: boolean; role: any; allPermissions: any[] }>()
const emit = defineEmits(['close'])

const selectedPermissions = ref<number[]>([])

watch(() => props.role, (role) => {
  if (role) {
    selectedPermissions.value = role.permissions_list
      .filter(p => p.is_attached)
      .map(p => p.id)
  }
}, { immediate: true })

const submit = () => {
  useForm({ permissions: selectedPermissions.value })
    .post(route('roles.permissions.assign', props.role.id), {
      onSuccess: () => emit('close'),
    })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Assign Permissions to: {{ props.role?.name }}</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div v-for="perm in props.allPermissions" :key="perm.id" class="flex items-center space-x-3">
            <input
              :id="'perm-' + perm.id"
              type="checkbox"
              :checked="selectedPermissions.includes(perm.id)"
              @change="val => val
                ? selectedPermissions.push(perm.id)
                : selectedPermissions = selectedPermissions.filter(id => id !== perm.id)"
            />
            <Label :for="'perm-' + perm.id" class="font-normal cursor-pointer">
              {{ perm.name }}
              <span v-if="perm.description" class="text-xs text-gray-500 block">{{ perm.description }}</span>
            </Label>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="emit('close')">Cancel</Button>
          <Button type="submit">Save Permissions</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>