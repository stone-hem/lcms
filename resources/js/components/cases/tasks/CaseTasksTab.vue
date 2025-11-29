<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { 
  CheckCircle2, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  Calendar,
  Clock,
  AlertCircle,
  Star,
  Filter,
  MoreVertical,
  User,
  Flag
} from 'lucide-vue-next'

const props = defineProps<{
  tasks?: any[]
}>()

const searchQuery = ref('')
const activeFilter = ref('all')

// Status options with colors
const statusOptions = [
  { value: 1, label: "Pending", color: "bg-yellow-100 text-yellow-800 border-yellow-200", icon: Clock },
  { value: 2, label: "In Progress", color: "bg-blue-100 text-blue-800 border-blue-200", icon: AlertCircle },
  { value: 3, label: "Completed", color: "bg-green-100 text-green-800 border-green-200", icon: CheckCircle2 },
  { value: 4, label: "Cancelled", color: "bg-red-100 text-red-800 border-red-200", icon: AlertCircle }
]

// Priority options
const priorityOptions = [
  { value: 1, label: "Low", color: "bg-gray-100 text-gray-800 border-gray-200" },
  { value: 2, label: "Medium", color: "bg-blue-100 text-blue-800 border-blue-200" },
  { value: 3, label: "High", color: "bg-orange-100 text-orange-800 border-orange-200" },
  { value: 4, label: "Critical", color: "bg-red-100 text-red-800 border-red-200" }
]

// Filter options
const filterOptions = [
  { value: 'all', label: 'All Tasks', count: 0 },
  { value: 'pending', label: 'Pending', count: 0 },
  { value: 'in-progress', label: 'In Progress', count: 0 },
  { value: 'completed', label: 'Completed', count: 0 },
  { value: 'high-priority', label: 'High Priority', count: 0 }
]

// Compute filtered tasks based on search and active filter
const filteredTasks = computed(() => {
  if (!props.tasks) return []
  
  let filtered = props.tasks
  
  // Apply search filter
  if (searchQuery.value) {
    filtered = filtered.filter(task => 
      task.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      task.assignee?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  
  // Apply status filter
  if (activeFilter.value !== 'all') {
    switch (activeFilter.value) {
      case 'pending':
        filtered = filtered.filter(task => task.status === 1)
        break
      case 'in-progress':
        filtered = filtered.filter(task => task.status === 2)
        break
      case 'completed':
        filtered = filtered.filter(task => task.status === 3)
        break
      case 'high-priority':
        filtered = filtered.filter(task => task.priority === 3 || task.priority === 4)
        break
    }
  }
  
  return filtered
})

// Update filter counts
const updateFilterCounts = computed(() => {
  if (!props.tasks) return filterOptions
  
  return filterOptions.map(filter => {
    let count = 0
    switch (filter.value) {
      case 'all':
        count = props.tasks.length
        break
      case 'pending':
        count = props.tasks.filter(task => task.status === 1).length
        break
      case 'in-progress':
        count = props.tasks.filter(task => task.status === 2).length
        break
      case 'completed':
        count = props.tasks.filter(task => task.status === 3).length
        break
      case 'high-priority':
        count = props.tasks.filter(task => task.priority === 3 || task.priority === 4).length
        break
    }
    return { ...filter, count }
  })
})

// Empty handlers for buttons (to be implemented later)
const handleAddTask = () => {
  console.log('Add task clicked')
}

const handleEditTask = (task: any) => {
  console.log('Edit task:', task)
}

const handleDeleteTask = (task: any) => {
  console.log('Delete task:', task)
}

const handleToggleComplete = (task: any) => {
  console.log('Toggle complete:', task)
}

// Format date for display
const formatDate = (dateString: string) => {
  if (!dateString) return 'No due date'
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

// Get priority badge
const getPriorityBadge = (priorityValue: number) => {
  const priority = priorityOptions.find(option => option.value === priorityValue)
  return priority || priorityOptions[0]
}

// Check if task is overdue
const isOverdue = (dueDate: string) => {
  if (!dueDate) return false
  return new Date(dueDate) < new Date()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header with Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card v-for="status in statusOptions" :key="status.value" class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-2">
            <div :class="[status.color.split(' ')[0], 'p-2 rounded-full bg-opacity-20']">
              <component :is="status.icon" class="h-4 w-4" :class="status.color.split(' ')[1]" />
            </div>
            <p class="text-2xl font-bold text-foreground">
              {{ tasks?.filter(task => task.status === status.value).length || 0 }}
            </p>
            <p class="text-sm text-muted-foreground">{{ status.label }}</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Sidebar Filters -->
      <div class="lg:col-span-1 space-y-4">
        <Card>
          <CardHeader class="pb-3">
            <CardTitle class="flex items-center gap-2 text-sm">
              <Filter class="h-4 w-4" />
              Filters
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <button
              v-for="filter in updateFilterCounts"
              :key="filter.value"
              @click="activeFilter = filter.value"
              :class="[
                'w-full flex items-center justify-between p-3 rounded-lg text-sm transition-colors',
                activeFilter === filter.value
                  ? 'bg-primary text-primary-foreground font-medium'
                  : 'hover:bg-muted text-muted-foreground'
              ]"
            >
              <span>{{ filter.label }}</span>
              <Badge variant="secondary" class="text-xs">
                {{ filter.count }}
              </Badge>
            </button>
          </CardContent>
        </Card>
      </div>

      <!-- Tasks List -->
      <div class="lg:col-span-3 space-y-4">
        <!-- Search and Add -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="relative flex-1 max-w-md">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search tasks..."
              class="pl-10"
            />
          </div>
          <Button @click="handleAddTask" class="flex items-center gap-2">
            <Plus class="h-4 w-4" />
            Add Task
          </Button>
        </div>

        <!-- Tasks -->
        <Card>
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <CardTitle>Tasks</CardTitle>
              <Badge variant="secondary" class="text-sm">
                {{ filteredTasks.length }} {{ filteredTasks.length === 1 ? 'task' : 'tasks' }}
              </Badge>
            </div>
            <CardDescription>
              Manage case-related tasks and deadlines
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="!filteredTasks.length" class="text-center py-8 text-muted-foreground">
              <CheckCircle2 class="h-12 w-12 mx-auto mb-4 opacity-50" />
              <p>No tasks found</p>
              <Button @click="handleAddTask" variant="outline" class="mt-4">
                <Plus class="h-4 w-4 mr-2" />
                Add First Task
              </Button>
            </div>

            <div v-else class="space-y-3">
              <div 
                v-for="task in filteredTasks" 
                :key="task.id"
                :class="[
                  'flex items-start justify-between p-4 rounded-lg border transition-colors',
                  task.status === 3 ? 'bg-muted/50 opacity-75' : 'bg-card hover:bg-muted/50'
                ]"
              >
                <div class="flex items-start gap-3 flex-1 min-w-0">
                  <!-- Checkbox -->
                  <button 
                    @click="handleToggleComplete(task)"
                    :class="[
                      'flex-shrink-0 w-5 h-5 rounded border mt-1 transition-colors',
                      task.status === 3 
                        ? 'bg-primary border-primary text-primary-foreground' 
                        : 'border-muted-foreground/30 hover:border-primary'
                    ]"
                  >
                    <CheckCircle2 v-if="task.status === 3" class="h-3 w-3 mx-auto" />
                  </button>

                  <!-- Task Content -->
                  <div class="flex-1 min-w-0 space-y-2">
                    <!-- Title and Badges -->
                    <div class="flex items-center gap-2 flex-wrap">
                      <h3 
                        :class="[
                          'font-medium text-foreground',
                          task.status === 3 ? 'line-through' : ''
                        ]"
                      >
                        {{ task.title }}
                      </h3>
                      <Badge 
                        :class="getStatusBadge(task.status).color"
                        class="border text-xs"
                      >
                        {{ getStatusBadge(task.status).label }}
                      </Badge>
                      <Badge 
                        v-if="task.priority"
                        :class="getPriorityBadge(task.priority).color"
                        class="border text-xs"
                      >
                        <Flag class="h-3 w-3 mr-1" />
                        {{ getPriorityBadge(task.priority).label }}
                      </Badge>
                    </div>

                    <!-- Description -->
                    <p 
                      v-if="task.description"
                      class="text-sm text-muted-foreground line-clamp-2"
                    >
                      {{ task.description }}
                    </p>

                    <!-- Meta Information -->
                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                      <!-- Due Date -->
                      <div 
                        v-if="task.due_date"
                        class="flex items-center gap-1"
                        :class="isOverdue(task.due_date) && task.status !== 3 ? 'text-red-600' : ''"
                      >
                        <Calendar class="h-3 w-3" />
                        <span>{{ formatDate(task.due_date) }}</span>
                        <span v-if="isOverdue(task.due_date) && task.status !== 3" class="text-red-600">
                          • Overdue
                        </span>
                      </div>

                      <!-- Assignee -->
                      <div v-if="task.assignee" class="flex items-center gap-1">
                        <User class="h-3 w-3" />
                        <span>{{ task.assignee.name || task.assignee.first_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1 ml-4">
                  <Button 
                    @click="handleEditTask(task)" 
                    variant="ghost" 
                    size="icon"
                    class="h-8 w-8"
                  >
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button 
                    @click="handleDeleteTask(task)" 
                    variant="ghost" 
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
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
</style>