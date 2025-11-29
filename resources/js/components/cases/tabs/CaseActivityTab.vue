<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { 
  Activity, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  Calendar,
  User,
  FileText,
  Clock,
  CheckCircle2,
  AlertCircle,
  PlayCircle
} from 'lucide-vue-next'

const props = defineProps<{
  activities?: any[]
}>()

const searchQuery = ref('')

// Status options with colors
const statusOptions = [
  { value: 1, label: "Pending", color: "bg-yellow-100 text-yellow-800 border-yellow-200" },
  { value: 2, label: "In Progress", color: "bg-blue-100 text-blue-800 border-blue-200" },
  { value: 3, label: "Completed", color: "bg-green-100 text-green-800 border-green-200" },
  { value: 4, label: "Cancelled", color: "bg-red-100 text-red-800 border-red-200" }
]

// Filter activities based on search
const filteredActivities = computed(() => {
  if (!props.activities) return []
  if (!searchQuery.value) return props.activities
  
  return props.activities.filter(activity => 
    activity.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    activity.activity?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    activity.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Empty handlers for buttons (to be implemented later)
const handleAddActivity = () => {
  console.log('Add activity clicked')
}

const handleEditActivity = (activity: any) => {
  console.log('Edit activity:', activity)
}

const handleDeleteActivity = (activity: any) => {
  console.log('Delete activity:', activity)
}

// Format date for display
const formatDate = (dateString: string) => {
  if (!dateString) return 'Not set'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Get status badge
const getStatusBadge = (statusValue: number) => {
  const status = statusOptions.find(option => option.value === statusValue)
  return status || statusOptions[0]
}

// Get activity icon based on type
const getActivityIcon = (activityType: string) => {
  const type = activityType?.toLowerCase() || ''
  if (type.includes('meeting') || type.includes('hearing')) return User
  if (type.includes('filing') || type.includes('document')) return FileText
  if (type.includes('deadline')) return Clock
  if (type.includes('review')) return CheckCircle2
  return Activity
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
          placeholder="Search activities..."
          class="pl-10"
        />
      </div>
      <Button @click="handleAddActivity" class="flex items-center gap-2">
        <Plus class="h-4 w-4" />
        Add Activity
      </Button>
    </div>

    <!-- Activities List -->
    <Card>
      <CardHeader class="pb-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Activity class="h-5 w-5 text-muted-foreground" />
            <CardTitle>Case Activities</CardTitle>
          </div>
          <Badge variant="secondary" class="text-sm">
            {{ activities?.length || 0 }} {{ (activities?.length || 0) === 1 ? 'activity' : 'activities' }}
          </Badge>
        </div>
        <CardDescription>
          All activities and events related to this case
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="!activities?.length" class="text-center py-8 text-muted-foreground">
          <Activity class="h-12 w-12 mx-auto mb-4 opacity-50" />
          <p>No activities added yet</p>
          <Button @click="handleAddActivity" variant="outline" class="mt-4">
            <Plus class="h-4 w-4 mr-2" />
            Add First Activity
          </Button>
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="activity in filteredActivities" 
            :key="activity.id"
            class="border rounded-lg hover:border-muted-foreground/20 transition-colors"
          >
            <!-- Activity Header -->
            <div class="flex items-start justify-between p-4">
              <div class="flex items-start gap-3 flex-1">
                <div class="flex-shrink-0 mt-1">
                  <div class="p-2 rounded-full bg-primary/10">
                    <component 
                      :is="getActivityIcon(activity.activity?.name)" 
                      class="h-4 w-4 text-primary" 
                    />
                  </div>
                </div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-medium text-foreground truncate">
                      {{ activity.title }}
                    </h3>
                    <Badge 
                      :class="getStatusBadge(activity.status).color"
                      class="border text-xs font-medium"
                    >
                      {{ getStatusBadge(activity.status).label }}
                    </Badge>
                  </div>
                  
                  <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1">
                      <Calendar class="h-3 w-3" />
                      <span>{{ formatDate(activity.date) }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                      <Activity class="h-3 w-3" />
                      <span>{{ activity.activity?.name || 'Activity' }}</span>
                    </div>
                  </div>

                  <!-- Participants -->
                  <div v-if="activity.participants?.length" class="mt-2">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                      <User class="h-3 w-3" />
                      <span class="font-medium">Participants:</span>
                      <span>
                        {{ activity.participants.map(p => p.user?.name || p.user?.first_name).join(', ') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center gap-1 ml-4">
                <Button 
                  @click="handleEditActivity(activity)" 
                  variant="ghost" 
                  size="icon"
                  class="h-8 w-8"
                >
                  <Edit class="h-4 w-4" />
                </Button>
                <Button 
                  @click="handleDeleteActivity(activity)" 
                  variant="ghost" 
                  size="icon"
                  class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>

            <!-- Activity Details (Collapsible) -->
            <div v-if="activity.description || activity.attachments?.length" class="border-t px-4 pb-4">
              <!-- Description -->
              <div v-if="activity.description" class="mt-3">
                <p class="text-sm text-muted-foreground mb-2 font-medium">Description</p>
                <p class="text-sm text-foreground">{{ activity.description }}</p>
              </div>

              <!-- Attachments -->
              <div v-if="activity.attachments?.length" class="mt-3">
                <p class="text-sm text-muted-foreground mb-2 font-medium">Attachments</p>
                <div class="grid gap-2">
                  <div 
                    v-for="attachment in activity.attachments" 
                    :key="attachment.id"
                    class="flex items-center gap-2 p-2 rounded border text-sm hover:bg-muted/50 transition-colors"
                  >
                    <FileText class="h-4 w-4 text-muted-foreground" />
                    <div class="flex-1 min-w-0">
                      <p class="text-foreground truncate">{{ attachment.file_name }}</p>
                      <p class="text-xs text-muted-foreground">
                        {{ attachment.extension?.toUpperCase() }} • 
                        {{ Math.round(attachment.size / 1024) }} KB
                      </p>
                    </div>
                    <Button variant="ghost" size="icon" class="h-6 w-6">
                      <PlayCircle class="h-3 w-3" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Search Results -->
          <div 
            v-if="filteredActivities.length === 0 && activities.length > 0"
            class="text-center py-8 text-muted-foreground"
          >
            <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No activities match your search</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Activity Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card v-for="status in statusOptions" :key="status.value" class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-2">
            <div :class="[status.color.split(' ')[0], 'p-2 rounded-full bg-opacity-20']">
              <Activity class="h-4 w-4" :class="status.color.split(' ')[1]" />
            </div>
            <p class="text-2xl font-bold text-foreground">
              {{ activities?.filter(a => a.status === status.value).length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">{{ status.label }}</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>