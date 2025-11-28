<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import { Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const props = defineProps<{
  items: Array<any>
  item_count: number
  presets: {
    document_types: Array<{ id: number; name: string }>
  }
  baseFilePath: string
}>()

// Local state
const search = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const loading = ref(false)

const templates = ref<any[]>(props.items)
const total = ref(props.item_count)

// Modal states
const showAdd = ref(false)
const showEdit = ref(false)
const showDelete = ref(false)
const selectedTemplate = ref<any>(null)

// Add Form
const addForm = ref({
  title: '',
  document_type_id: '',
  description: '',
  file: null as File | null,
  file_name: '',
  file_description: ''
})

// Edit Form
const editForm = ref({
  title: '',
  document_type_id: '',
  description: '',
  file_id: null as number | null,
  file_name: '',
  file_description: ''
})

// Load templates with filters
const load = () => {
  loading.value = true
  router.get(route('knowledge.templates.index'), {
    s: search.value || undefined,
    p: (currentPage.value - 1) * perPage.value,
    ipp: perPage.value
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['items', 'item_count'],
    onFinish: () => {
      templates.value = props.items
      total.value = props.item_count
      loading.value = false
    }
  })
}

watch(search, () => {
  currentPage.value = 1
  load()
})

// Open Add Modal
const openAdd = () => {
  addForm.value = {
    title: '',
    document_type_id: '',
    description: '',
    file: null,
    file_name: '',
    file_description: ''
  }
  showAdd.value = true
}

// Open Edit Modal
const openEdit = (template: any) => {
  selectedTemplate.value = template
  editForm.value = {
    title: template.title,
    document_type_id: template.document_type_id,
    description: template.description || '',
    file_id: template.templates[0]?.id || null,
    file_name: template.templates[0]?.file_name || '',
    file_description: template.templates[0]?.description || ''
  }
  showEdit.value = true
}

// Confirm Delete
const confirmDelete = (template: any) => {
  selectedTemplate.value = template
  showDelete.value = true
}

// Submit Add
const submitAdd = () => {
  if (!addForm.value.file) return

  const formData = new FormData()
  formData.append('title', addForm.value.title)
  formData.append('document_type_id', addForm.value.document_type_id)
  formData.append('description', addForm.value.description)
  formData.append('file', addForm.value.file)
  formData.append('file_name', addForm.value.file_name)
  formData.append('file_description', addForm.value.file_description)

  router.post(route('knowledge.templates.store'), formData, {
    forceFormData: true,
    onSuccess: () => {
      showAdd.value = false
      load()
    }
  })
}

// Submit Edit
const submitEdit = () => {
  const formData = new FormData()
  formData.append('_method', 'PUT')
  formData.append('title', editForm.value.title)
  formData.append('document_type_id', editForm.value.document_type_id)
  formData.append('description', editForm.value.description)
  if (editForm.value.file_id) {
    formData.append('file_id', editForm.value.file_id.toString())
  }
  formData.append('file_name', editForm.value.file_name)
  formData.append('file_description', editForm.value.file_description)

  router.post(route('knowledge.templates.update', selectedTemplate.value.id), formData, {
    forceFormData: true,
    onSuccess: () => {
      showEdit.value = false
      load()
    }
  })
}

// Delete
const deleteTemplate = () => {
  router.delete(route('knowledge.templates.destroy', selectedTemplate.value.id), {
    onSuccess: () => {
      showDelete.value = false
      load()
    }
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Document Templates" />

    <div class="space-y-6 md:p-8 p-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Document Templates</h1>
        <div class="flex gap-3">
          <Input v-model="search" placeholder="Search templates..." class="w-64" />
          <Button @click="openAdd">
            <Plus class="w-5 h-5 mr-2" />
            Add Template
          </Button>
        </div>
      </div>

      <!-- Table -->
      <Card>
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Type</TableHead>
                <TableHead>Title</TableHead>
                <TableHead>File</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="t in templates" :key="t.id">
                <TableCell class="px-6 py-4 text-sm">{{ t.document_type?.name || '—' }}</TableCell>
                <TableCell class="px-6 py-4 text-sm font-medium">{{ t.title }}</TableCell>
                <TableCell class="px-6 py-4 text-sm">
                  <a :href="`${props.baseFilePath}${t.templates[0]?.file_name}`" target="_blank" class="text-blue-600 hover:underline">
                    {{ t.templates[0]?.file_name || '—' }}
                  </a>
                </TableCell>
                <TableCell class="px-6 py-4 text-sm text-slate-600 max-w-md truncate">
                  {{ t.description || '—' }}
                </TableCell>
                <TableCell class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-4">
                    <button @click="openEdit(t)" class="text-slate-600 hover:text-primary-600">
                      <Pencil class="w-5 h-5" />
                    </button>
                    <a :href="`${props.baseFilePath}${t.templates[0]?.file_name}`" target="_blank" class="text-slate-600 hover:text-blue-600">
                      <Eye class="w-5 h-5" />
                    </a>
                    <button @click="confirmDelete(t)" class="text-slate-600 hover:text-red-600">
                      <Trash2 class="w-5 h-5" />
                    </button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <!-- Empty / Loading -->
          <div v-if="!templates.length && !loading" class="text-center py-12 text-slate-500">
            No templates found.
          </div>
          <div v-if="loading" class="text-center py-12">
            <div class="inline-block w-10 h-10 border-4 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center px-6 py-4 border-t">
          <p class="text-sm text-slate-600">Showing {{ templates.length }} of {{ total }} templates</p>
          <div class="flex gap-2">
            <Button :disabled="currentPage === 1" @click="currentPage--; load()" variant="outline">Previous</Button>
            <Button :disabled="templates.length < perPage" @click="currentPage++; load()" variant="outline">Next</Button>
          </div>
        </div>
      </Card>
    </div>

    <!-- Add Modal -->
    <Dialog :open="showAdd" @update:open="showAdd = false">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Add Document Template</DialogTitle>
          <DialogDescription>Upload one file with proper metadata</DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitAdd" class="space-y-5">
          <div>
            <Label>Title <span class="text-red-600">*</span></Label>
            <Input v-model="addForm.title" required />
          </div>
          <div>
            <Label>Document Type <span class="text-red-600">*</span></Label>
            <select v-model="addForm.document_type_id" required class="w-full px-3 py-2 border rounded-lg">
              <option value="">Select type</option>
              <option v-for="type in props.presets.document_types" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>
          <div>
            <Label>Description</Label>
            <TextArea v-model="addForm.description" rows="3" />
          </div>
          <div>
            <Label>File <span class="text-red-600">*</span></Label>
            <input type="file" @change="e => addForm.file = (e.target as HTMLInputElement).files?.[0] || null" required class="w-full" />
            <p v-if="addForm.file" class="text-sm text-slate-600 mt-2">{{ addForm.file.name }}</p>
          </div>
          <div>
            <Label>File Display Name</Label>
            <Input v-model="addForm.file_name" placeholder="e.g. Client Agreement v1.0" />
          </div>
          <div>
            <Label>File Description</Label>
            <TextArea v-model="addForm.file_description" placeholder="Purpose of this template..." rows="2" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showAdd = false">Cancel</Button>
            <Button type="submit">Create Template</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="showEdit" @update:open="showEdit = false">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Edit Template</DialogTitle>
        </DialogHeader>
        <form @submit.prevent="submitEdit" class="space-y-5">
          <div>
            <Label>Title</Label>
            <Input v-model="editForm.title" required />
          </div>
          <div>
            <Label>Document Type</Label>
            <select v-model="editForm.document_type_id" required class="w-full px-3 py-2 border rounded-lg">
              <option v-for="type in props.presets.document_types" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>
          <div>
            <Label>Description</Label>
            <TextArea v-model="editForm.description" rows="3" />
          </div>
          <div>
            <Label>Current File</Label>
            <a :href="`${props.baseFilePath}${editForm.file_name}`" target="_blank" class="text-blue-600 underline">
              {{ editForm.file_name || 'No file' }}
            </a>
          </div>
          <div>
            <Label>File Display Name</Label>
            <Input v-model="editForm.file_name" />
          </div>
          <div>
            <Label>File Description</Label>
            <TextArea v-model="editForm.file_description" rows="2" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" @click="showEdit = false">Cancel</Button>
            <Button type="submit">Update Template</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirm -->
    <Dialog :open="showDelete" @update:open="showDelete = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Template?</DialogTitle>
        </DialogHeader>
        <p>Delete "<strong>{{ selectedTemplate?.title }}</strong>"? This cannot be undone.</p>
        <DialogFooter>
          <Button variant="outline" @click="showDelete = false">Cancel</Button>
          <Button @click="deleteTemplate" class="bg-red-600 hover:bg-red-700">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>