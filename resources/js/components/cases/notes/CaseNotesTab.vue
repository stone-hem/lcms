<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { 
  FileText, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  Calendar,
  Paperclip,
  Eye
} from 'lucide-vue-next'
import AddCaseNoteModal from '@/components/cases/notes/AddCaseNoteModal.vue'
import EditCaseNoteModal from '@/components/cases/notes/EditCaseNoteModal.vue'
import DeleteCaseNoteModal from '@/components/cases/notes/DeleteCaseNoteModal.vue'

const props = defineProps<{
  notes?: any[],
  legalCaseId: number,
}>()

const searchQuery = ref('')

// Filter notes based on search
const filteredNotes = computed(() => {
  if (!props.notes) return []
  if (!searchQuery.value) return props.notes
  
  return props.notes.filter(note => 
    note.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    note.note?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const modals = {
  add: ref(false),
  edit: ref(false),
  delete: ref(false),
}

const selectedNote = ref(null)

const reload = () => router.reload({ only: ['notes'] })

const handleAddNote = () => (modals.add.value = true)
const handleEditNote = (note: any) => {
  selectedNote.value = note
  modals.edit.value = true
}
const handleDeleteNote = (note: any) => {
  selectedNote.value = note
  modals.delete.value = true
}

const handleViewAttachment = (attachment: any) => {
  console.log('View attachment:', attachment)
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
</script>

<template>
  <div class="space-y-6">
    <!-- Search and Add Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="searchQuery"
          placeholder="Search notes..."
          class="pl-10"
        />
      </div>
      <Button @click="handleAddNote" class="flex items-center gap-2">
        <Plus class="h-4 w-4" />
        Add Note
      </Button>
    </div>

    <!-- Notes Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="note in filteredNotes" 
        :key="note.id"
        class="hover:shadow-lg transition-shadow duration-200"
      >
        <CardHeader class="pb-3">
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <CardTitle class="text-base mb-2 line-clamp-2">
                {{ note.title }}
              </CardTitle>
              <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <Calendar class="h-4 w-4" />
                <span>{{ formatDate(note.date) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-1 ml-2">
              <Button 
                @click="handleEditNote(note)" 
                variant="ghost" 
                size="icon"
                class="h-8 w-8"
              >
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
                @click="handleDeleteNote(note)" 
                variant="ghost" 
                size="icon"
                class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardHeader>
        
        <CardContent class="space-y-4">
          <!-- Note Content -->
          <div class="text-sm text-foreground">
            <p class="line-clamp-4">{{ note.note }}</p>
          </div>

          <!-- Attachments -->
          <div v-if="note.attachments?.length" class="space-y-2">
            <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
              <Paperclip class="h-4 w-4" />
              <span>Attachments ({{ note.attachments.length }})</span>
            </div>
            <div class="space-y-2">
              <div 
                v-for="attachment in note.attachments" 
                :key="attachment.id"
                class="flex items-center gap-3 p-2 rounded-lg border text-sm hover:bg-muted/50 transition-colors"
              >
                <FileText class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                <div class="flex-1 min-w-0">
                  <p class="text-foreground truncate">{{ attachment.file_name }}</p>
                  <p class="text-xs text-muted-foreground">
                    {{ attachment.extension?.toUpperCase() }} • 
                    {{ formatFileSize(attachment.size) }}
                  </p>
                </div>
                <Button 
                  @click="handleViewAttachment(attachment)" 
                  variant="ghost" 
                  size="icon"
                  class="h-6 w-6"
                >
                  <Eye class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>

          <!-- View Full Note Button -->
          <Button variant="outline" size="sm" class="w-full">
            View Full Note
          </Button>
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <div 
      v-if="!filteredNotes.length" 
      class="text-center py-12"
    >
      <div class="max-w-sm mx-auto">
        <FileText class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
        <h3 class="text-lg font-medium text-foreground mb-2">No notes yet</h3>
        <p class="text-muted-foreground mb-6">
          Get started by creating your first case note to keep track of important information.
        </p>
        <Button @click="handleAddNote" class="flex items-center gap-2 mx-auto">
          <Plus class="h-4 w-4" />
          Create First Note
        </Button>
      </div>
    </div>

    <!-- No Search Results -->
    <div 
      v-if="filteredNotes.length === 0 && props.notes?.length"
      class="text-center py-12"
    >
      <Search class="h-16 w-16 mx-auto mb-4 text-muted-foreground opacity-50" />
      <h3 class="text-lg font-medium text-foreground mb-2">No notes found</h3>
      <p class="text-muted-foreground">
        Try adjusting your search terms to find what you're looking for.
      </p>
    </div>

    <!-- Notes Statistics -->
    <Card>
      <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2 text-sm">
          <FileText class="h-4 w-4" />
          Notes Summary
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4 rounded-lg bg-muted/50">
            <p class="text-2xl font-bold text-foreground">
              {{ props.notes?.length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">Total Notes</p>
          </div>
          <div class="text-center p-4 rounded-lg bg-muted/50">
            <p class="text-2xl font-bold text-foreground">
              {{ props.notes?.filter(note => note.attachments?.length).length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">With Attachments</p>
          </div>
          <div class="text-center p-4 rounded-lg bg-muted/50">
            <p class="text-2xl font-bold text-foreground">
              {{ props.notes?.reduce((total, note) => total + (note.attachments?.length || 0), 0) || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">Total Attachments</p>
          </div>
          <div class="text-center p-4 rounded-lg bg-muted/50">
            <p class="text-2xl font-bold text-foreground">
              {{ props.notes?.filter(note => new Date(note.date) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)).length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">Last 7 Days</p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
  <AddCaseNoteModal
      :open="modals.add.value"
      :legal-case-id="legalCaseId"
      @close="modals.add.value = false"
      @success="reload"
    />
  
    <EditCaseNoteModal
      :open="modals.edit.value"
      :note="selectedNote"
      @close="modals.edit.value = false"
      @success="reload"
    />
  
    <DeleteCaseNoteModal
      :open="modals.delete.value"
      :note="selectedNote"
      @close="modals.delete.value = false"
      @deleted="reload"
    />
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>