<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const props = defineProps<{ items: any[] }>()
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, claim: '', description: '' })

const openAdd = () => { isEdit.value = false; form.reset(); showModal.value = true }
const openEdit = (item: any) => { isEdit.value = true; form.id = item.id; form.claim = item.claim; form.description = item.description || ''; showModal.value = true }

const submit = () => {
  isEdit.value
    ? form.put(route('misc.update_nature_of_claim', form.id), { onSuccess: () => showModal.value = false })
    : form.post(route('misc.store_nature_of_claim'), { onSuccess: () => showModal.value = false })
}

const deactivate = (id: number) => confirm('Deactivate?') && router.post(route('misc.deactivate_nature_of_claim', id))
const activate = (id: number) => router.post(route('misc.activate_nature_of_claim', id))
const destroy = (id: number) => confirm('Delete permanently?') && router.post(route('misc.delete_nature_of_claim', id))
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Nature of Claims</CardTitle>
      <Button @click="openAdd">Add Claim</Button>
    </CardHeader>
    <CardContent>
      <div class="rounded-md border">
        <table class="w-full text-sm">
          <thead class="bg-muted">
            <tr>
              <th class="text-left p-4">Claim</th>
              <th class="text-left p-4">Description</th>
              <th class="text-left p-4">Status</th>
              <th class="text-right p-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="item in items" :key="item.id">
              <td class="p-4">{{ item.claim }}</td>
              <td class="p-4 text-muted-foreground">{{ item.description || '—' }}</td>
              <td class="p-4">
                <span :class="item.deleted_at ? 'text-destructive' : 'text-green-600'">
                  {{ item.deleted_at ? 'Inactive' : 'Active' }}
                </span>
              </td>
              <td class="p-4 text-right space-x-2">
                <Button size="sm" variant="ghost" @click="openEdit(item)">Edit</Button>
                <Button v-if="item.deleted_at" size="sm" variant="ghost" @click="activate(item.id)">Activate</Button>
                <Button v-else size="sm" variant="ghost" @click="deactivate(item.id)">Deactivate</Button>
                <Button size="sm" variant="destructive" @click="destroy(item.id)">Delete</Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </CardContent>
  </Card>

  <Dialog :open="showModal" @update:open="showModal = false">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ isEdit ? 'Edit' : 'Add' }} Nature of Claim</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Claim <span class="text-red-600">*</span></Label>
          <Input v-model="form.claim" required placeholder="e.g. Breach of Contract" />
          <p v-if="form.errors.claim" class="text-sm text-red-600 mt-1">{{ form.errors.claim }}</p>
        </div>
        <div>
          <Label>Description</Label>
          <textarea v-model="form.description" rows="3" class="w-full rounded-md border px-3 py-2 text-sm"></textarea>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showModal = false">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Save</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>