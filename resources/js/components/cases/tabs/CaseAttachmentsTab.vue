<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { 
  Paperclip, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  Download,
  Eye,
  FileText,
  Calendar,
  FolderOpen,
  MoreVertical
} from 'lucide-vue-next'

const props = defineProps<{
  attachments?: any[]
}>()

const searchQuery = ref('')

// Filter attachments based on search
const filteredAttachments = computed(() => {
  if (!props.attachments) return []
  if (!searchQuery.value) return props.attachments
  
  return props.attachments.filter(attachment => 
    attachment.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    attachment.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    attachment.temp_type?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    attachment.files?.some((file: any) => 
      file.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})

// Group attachments by type
const groupedAttachments = computed(() => {
  const groups: Record<string, any[]> = {}
  
  filteredAttachments.value.forEach(attachment => {
    const type = attachment.temp_type || 'Other'
    if (!groups[type]) {
      groups[type] = []
    }
    groups[type].push(attachment)
  })
  
  return groups
})

// Get total file count
const totalFiles = computed(() => {
  if (!props.attachments) return 0
  return props.attachments.reduce((total, attachment) => 
    total + (attachment.files?.length || 0), 0
  )
})

// Empty handlers for buttons (to be implemented later)
const handleAddAttachment = () => {
  console.log('Add attachment clicked')
}

const handleEditAttachment = (attachment: any) => {
  console.log('Edit attachment:', attachment)
}

const handleDeleteAttachment = (attachment: any) => {
  console.log('Delete attachment:', attachment)
}

const handleDownloadFile = (file: any) => {
  console.log('Download file:', file)
}

const handleViewFile = (file: any) => {
  console.log('View file:', file)
}

// Format date for display
const formatDate = (dateString: string) => {
  if (!dateString) return 'No date'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Format file size
const formatFileSize = (bytes: number) => {
  if (!bytes) return '0 KB'
  const kb = Math.round(bytes / 1024)
  return kb < 1024 ? `${kb} KB` : `${(kb / 1024).toFixed(1)} MB`
}

// Get file icon based on extension
const getFileIcon = (fileName: string) => {
  const extension = fileName.split('.').pop()?.toLowerCase()
  switch (extension) {
    case 'pdf':
      return FileText
    case 'doc':
    case 'docx':
      return FileText
    case 'xls':
    case 'xlsx':
      return FileText
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif':
      return FileText
    case 'zip':
    case 'rar':
      return FolderOpen
    default:
      return FileText
  }
}

// Get file type color
const getFileTypeColor = (fileName: string) => {
  const extension = fileName.split('.').pop()?.toLowerCase()
  switch (extension) {
    case 'pdf':
      return 'bg-red-100 text-red-800 border-red-200'
    case 'doc':
    case 'docx':
      return 'bg-blue-100 text-blue-800 border-blue-200'
    case 'xls':
    case 'xlsx':
      return 'bg-green-100 text-green-800 border-green-200'
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif':
      return 'bg-purple-100 text-purple-800 border-purple-200'
    case 'zip':
    case 'rar':
      return 'bg-orange-100 text-orange-800 border-orange-200'
    default:
      return 'bg-gray-100 text-gray-800 border-gray-200'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header with Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-2">
            <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900">
              <FolderOpen class="h-4 w-4 text-blue-600 dark:text-blue-400" />
            </div>
            <p class="text-2xl font-bold text-foreground">
              {{ props.attachments?.length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">Attachment Groups</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-2">
            <div class="p-2 rounded-full bg-green-100 dark:bg-green-900">
              <FileText class="h-4 w-4 text-green-600 dark:text-green-400" />
            </div>
            <p class="text-2xl font-bold text-foreground">
              {{ totalFiles }}
            </p>
            <p class="text-sm text-muted-foreground">Total Files</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-2">
            <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900">
              <Paperclip class="h-4 w-4 text-purple-600 dark:text-purple-400" />
            </div>
            <p class="text-2xl font-bold text-foreground">
              {{ Object.keys(groupedAttachments).length }}
            </p>
            <p class="text-sm text-muted-foreground">Document Types</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Search and Add Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="searchQuery"
          placeholder="Search attachments..."
          class="pl-10"
        />
      </div>
      <Button @click="handleAddAttachment" class="flex items-center gap-2">
        <Plus class="h-4 w-4" />
        Add Attachment
      </Button>
    </div>

    <!-- Attachments by Type -->
    <div class="space-y-6">
      <div 
        v-for="[type, typeAttachments] in Object.entries(groupedAttachments)" 
        :key="type"
      >
        <Card>
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <FolderOpen class="h-5 w-5 text-muted-foreground" />
                <CardTitle class="text-lg">{{ type }}</CardTitle>
                <Badge variant="secondary" class="text-sm">
                  {{ typeAttachments.length }} {{ typeAttachments.length === 1 ? 'item' : 'items' }}
                </Badge>
              </div>
            </div>
          </CardHeader>
          <CardContent class="space-y-4">
            <div 
              v-for="attachment in typeAttachments" 
              :key="attachment.id"
              class="border rounded-lg hover:border-muted-foreground/20 transition-colors"
            >
              <!-- Attachment Header -->
              <div class="flex items-start justify-between p-4">
                <div class="flex items-start gap-3 flex-1">
                  <div class="flex-shrink-0 mt-1">
                    <div class="p-2 rounded-full bg-primary/10">
                      <Paperclip class="h-4 w-4 text-primary" />
                    </div>
                  </div>
                  
                  <div class="flex-1 min-w-0">
                    <h3 class="font-medium text-foreground mb-1">
                      {{ attachment.title?.trim() || 'Untitled Document' }}
                    </h3>
                    
                    <div class="flex items-center gap-4 text-sm text-muted-foreground mb-2">
                      <div class="flex items-center gap-1">
                        <Calendar class="h-3 w-3" />
                        <span v-if="attachment.date_uploaded">
                          {{ formatDate(attachment.date_uploaded) }}
                        </span>
                        <span v-else>No date</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <FileText class="h-3 w-3" />
                        <span>{{ attachment.files?.length || 0 }} files</span>
                      </div>
                    </div>

                    <!-- Description -->
                    <p 
                      v-if="attachment.description"
                      class="text-sm text-muted-foreground line-clamp-2"
                    >
                      {{ attachment.description }}
                    </p>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1 ml-4">
                  <Button 
                    @click="handleEditAttachment(attachment)" 
                    variant="ghost" 
                    size="icon"
                    class="h-8 w-8"
                  >
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button 
                    @click="handleDeleteAttachment(attachment)" 
                    variant="ghost" 
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </div>

              <!-- Files List -->
              <div v-if="attachment.files?.length" class="border-t px-4 pb-4">
                <div class="mt-3">
                  <p class="text-sm font-medium text-muted-foreground mb-2">Files</p>
                  <div class="grid gap-2">
                    <div 
                      v-for="file in attachment.files" 
                      :key="file.id"
                      class="flex items-center gap-3 p-3 rounded-lg border text-sm hover:bg-muted/50 transition-colors"
                    >
                      <div class="flex-shrink-0">
                        <div :class="[getFileTypeColor(file.name), 'p-2 rounded-lg']">
                          <component 
                            :is="getFileIcon(file.name)" 
                            class="h-4 w-4" 
                          />
                        </div>
                      </div>
                      
                      <div class="flex-1 min-w-0">
                        <p class="text-foreground font-medium truncate">
                          {{ file.name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                          {{ file.extension?.toUpperCase() }} • 
                          {{ formatFileSize(file.size) }}
                        </p>
                      </div>
                      
                      <div class="flex items-center gap-1">
                        <Button 
                          @click="handleViewFile(file)" 
                          variant="ghost" 
                          size="icon"
                          class="h-8 w-8"
                        >
                          <Eye class="h-4 w-4" />
                        </Button>
                        <Button 
                          @click="handleDownloadFile(file)" 
                          variant="ghost" 
                          size="icon"
                          class="h-8 w-8"
                        >
                          <Download class="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Empty State -->
    <div 
      v-if="!filteredAttachments.length" 
      class="text-center py-12"
    >
      <div class="max-w-sm mx-auto">
        <Paperclip class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
        <h3 class="text-lg font-medium text-foreground mb-2">No attachments yet</h3>
        <p class="text-muted-foreground mb-6">
          Add documents, images, and other files related to this case.
        </p>
        <Button @click="handleAddAttachment" class="flex items-center gap-2 mx-auto">
          <Plus class="h-4 w-4" />
          Add First Attachment
        </Button>
      </div>
    </div>

    <!-- No Search Results -->
    <div 
      v-if="filteredAttachments.length === 0 && props.attachments?.length"
      class="text-center py-12"
    >
      <Search class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
      <h3 class="text-lg font-medium text-foreground mb-2">No attachments found</h3>
      <p class="text-muted-foreground">
        Try adjusting your search terms to find what you're looking for.
      </p>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>