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
const form = useForm({ id: null, name: '', abbreviation: '', description: '' })

const openAdd = () => { isEdit.value = false; form.reset(); showModal.value = true }
const openEdit = (item: any) => {
  isEdit.value = true
  form.id = item.id
  form.name = item.name
  form.abbreviation = item.abbreviation || ''
  form.description = item.description || ''
  showModal.value = true
}

const submit = () => {
  isEdit.value
    ? form.put(route('misc.update_document_type', form.id), { onSuccess: () => showModal.value = false })
    : form.post(route('misc.store_document_type'), { onSuccess: () => showModal.value = false })
}

const deactivate = (id: number) => confirm('Deactivate?') && router.post(route('misc.deactivate_document_type', id))
const activate = (id: number) => router.post(route('misc.activate_document_type', id))
const destroy = (id: number) => confirm('Delete permanently?') && router.post(route('misc.delete_document_type', id))
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between">
      <CardTitle>Document Types</CardTitle>
      <Button @click="openAdd">Add Document Type</Button>
    </CardHeader>
    <CardContent>
      <div class="rounded-md border">
        <table class="w-full text-sm">
          <thead class="bg-muted">
            <tr>
              <th class="text-left p-4">Name</th>
              <th class="text-left p-4">Abbreviation</th>
              <th class="text-left p-4">Description</th>
              <th class="text-left p-4">Status</th>
              <th class="text-right p-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="item in items" :key="item.id">
              <td class="p-4">{{ item.name }}</td>
              <td class="p-4 font-mono text-xs">{{ item.abbreviation || '—' }}</td>
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
        <DialogTitle>{{ isEdit ? 'Edit' : 'Add' }} Document Type</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label>Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.name" required placeholder="e.g. Affidavit" />
        </div>
        <div>
          <Label>Abbreviation</Label>
          <Input v-model="form.abbreviation" placeholder="e.g. AFF" />
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