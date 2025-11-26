<!-- resources/js/Pages/files/Index.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import FileTreeItem from '@/components/files/FileTreeItem.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Search, FolderOpen, Folder, File } from 'lucide-vue-next'

const props = defineProps<{
  legal_cases: any[]
  base_file_path: string
}>()

const search = ref('')

// Transform raw data into nested file structure
const fileStructure = computed(() => {
  if (!props.legal_cases || props.legal_cases.length === 0) return []

  return props.legal_cases.map(caseItem => {
    const caseFolder = {
      id: `case-${caseItem.id}`,
      name: `${caseItem.case_number} - ${caseItem.title}`,
      type: 'folder' as const,
      children: [] as any[]
    }

    // Case Attachments
    if (caseItem.file_attachments?.case?.length > 0) {
      const caseFilesFolder = {
        name: 'Case Files',
        type: 'folder' as const,
        children: parseAttachments(caseItem.file_attachments.case)
      }
      if (caseFilesFolder.children.length > 0) caseFolder.children.push(caseFilesFolder)
    }

    // Activities
    if (caseItem.file_attachments?.activity?.length > 0) {
      const activitiesFolder = {
        name: 'Activities',
        type: 'folder' as const,
        children: caseItem.file_attachments.activity
          .filter(a => a.attachments && a.attachments.length > 0)
          .map(act => ({
            name: act.title || `Activity ${act.id}`,
            type: 'folder' as const,
            children: parseAttachments(act.attachments)
          }))
      }
      if (activitiesFolder.children.length > 0) caseFolder.children.push(activitiesFolder)
    }

    // Tasks
    if (caseItem.file_attachments?.tasks?.length > 0) {
      const tasksFolder = {
        name: 'Tasks',
        type: 'folder' as const,
        children: caseItem.file_attachments.tasks
          .filter(t => t.attachments?.length > 0)
          .map(task => ({
            name: task.title || `Task ${task.id}`,
            type: 'folder' as const,
            children: task.attachments.flatMap(att => parseAttachments(att.files || att))
          }))
      }
      if (tasksFolder.children.length > 0) caseFolder.children.push(tasksFolder)
    }

    // Notes
    if (caseItem.file_attachments?.notes?.length > 0) {
      const notesFolder = {
        name: 'Notes',
        type: 'folder' as const,
        children: caseItem.file_attachments.notes
          .filter(n => n.attachments && n.attachments.length > 0)
          .map(note => ({
            name: note.title || `Note ${note.id}`,
            type: 'folder' as const,
            children: parseAttachments(note.attachments)
          }))
      }
      if (notesFolder.children.length > 0) caseFolder.children.push(notesFolder)
    }

    // Fallback if no files
    if (caseFolder.children.length === 0) {
      caseFolder.children.push({
        name: 'No files found',
        type: 'file' as const,
        disabled: true
      })
    }

    return caseFolder
  })
})

// Helper: safely parse JSON or handle array/string
function parseAttachments(attachments: any): any[] {
  const files: any[] = []

  attachments.forEach((item: any) => {
    let list = item
    if (typeof item === 'string') {
      try { list = JSON.parse(item) } catch (e) { list = [] }
    }
    if (!Array.isArray(list)) list = [list]

    list.forEach((file: any) => {
      if (file && (file.file_name || file.name)) {
        files.push({
          id: file.id || Math.random().toString(36),
          name: file.file_name || file.name,
          extension: (file.file_name || file.name || '').split('.').pop()?.toLowerCase() || '',
          path: file.file_path || file.path || file.file_name || file.name
        })
      }
    })
  })

  return files
}

// Recursive search filter
function filterTree(items: any[], query: string): any[] {
  if (!query) return items

  return items
    .map(item => {
      if (item.type === 'folder') {
        const filtered = filterTree(item.children, query)
        if (filtered.length > 0 || item.name.toLowerCase().includes(query.toLowerCase())) {
          return { ...item, children: filtered }
        }
      } else if (item.name.toLowerCase().includes(query.toLowerCase())) {
        return item
      }
      return null
    })
    .filter(Boolean) as any[]
}

const filteredStructure = computed(() => filterTree(fileStructure.value, search.value))
</script>

<template>
  <AppLayout>
    <Head title="All Files & Documents" />

    <div class="p-8 space-y-8">
      <div class="flex justify-between items-center">
        <HeadingSmall
          title="All Files"
          description="Browse all documents across legal cases, tasks, activities & notes"
        />

        <div class="w-96">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search files or folders..."
              class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-black rounded-xl shadow-sm border">
        <div class="max-h-screen overflow-y-auto">
          <div v-if="filteredStructure.length === 0" class="text-center py-16 text-gray-500">
            <Folder class="h-16 w-16 mx-auto mb-4 text-gray-300" />
            <p class="text-lg">No files found</p>
            <p v-if="search" class="text-sm mt-2">Try adjusting your search</p>
          </div>

          <div v-else class="divide-y">
            <FileTreeItem
              v-for="item in filteredStructure"
              :key="item.id"
              :item="item"
              :level="0"
              :base-path="base_file_path"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>