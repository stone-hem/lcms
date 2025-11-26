<script setup lang="ts">
import { ref, computed } from 'vue'
import { Folder, FolderOpen, File, Eye, Download } from 'lucide-vue-next'

const props = defineProps<{
  item: any
  level: number
  basePath: string
}>()

const isOpen = ref(false)
const paddingLeft = computed(() => `${props.level * 24}px`)

function toggle() {
  if (props.item.type === 'folder') isOpen.value = !isOpen.value
}

function getFileIcon(ext: string) {
  const icons: Record<string, any> = {
    pdf: File,
    doc: File,
    docx: File,
    xls: File,
    xlsx: File,
    ppt: File,
    pptx: File,
    jpg: File,
    jpeg: File,
    png: File,
    gif: File,
    zip: File,
    rar: File,
  }
  return icons[ext] || File
}
</script>

<template>
  <div>
    <div
      :style="{ paddingLeft }"
      class="flex items-center justify-between py-2 px-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer group"
      @click="toggle"
    >
      <div class="flex items-center space-x-3 flex-1 min-w-0">
        <!-- Icon -->
        <component
          :is="item.type === 'folder' ? (isOpen ? FolderOpen : Folder) : getFileIcon(item.extension)"
          class="h-5 w-5 flex-shrink-0"
          :class="item.type === 'folder' ? 'text-yellow-600' : 'text-gray-600'"
        />

        <!-- Name -->
        <span
          :class="{
            'font-medium': item.type === 'folder',
            'text-gray-500': item.disabled
          }"
          class="truncate"
        >
          {{ item.name }}
        </span>

        <!-- Count badge -->
        <span v-if="item.type === 'folder' && item.children?.length" class="text-xs text-gray-400">
          ({{ item.children.length }})
        </span>
      </div>

      <!-- Actions (only for files) -->
      <div v-if="item.type === 'file' && !item.disabled" class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition">
        <a
          :href="`${basePath}${item.path || item.name}`"
          target="_blank"
          class="p-1 hover:bg-gray-200 rounded"
          @click.stop
        >
          <Eye class="h-4 w-4 text-blue-600" />
        </a>
        <a
          :href="`${basePath}${item.path || item.name}`"
          download
          class="p-1 hover:bg-gray-200 rounded"
          @click.stop
        >
          <Download class="h-4 w-4 text-green-600" />
        </a>
      </div>
    </div>

    <!-- Children -->
    <div v-if="item.type === 'folder' && isOpen && item.children">
      <FileTreeItem
        v-for="child in item.children"
        :key="child.id || child.name"
        :item="child"
        :level="level + 1"
        :base-path="basePath"
      />
    </div>
  </div>
</template>