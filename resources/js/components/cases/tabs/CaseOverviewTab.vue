<script setup lang="ts">
import { computed, ref } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Calendar, FileText, Users, Activity, CheckCircle2, Clock, AlertCircle } from 'lucide-vue-next'

const props = defineProps<{
  case: {
    id: string
    title: string
    case_number: string
    description?: string
    type?: { name: string }
    stage?: { name: string }
    date_received?: string
    date_of_filing?: string
    completion_date?: string
    individual_parties?: any[]
    firm_parties?: any[]
    tasks?: any[]
    activities?: any[]
    notes?: any[]
    attachments?: any[]
    events?: any[]
  }
}>()

const caseData = props.case

// Statistics data
const statistics = computed(() => [
  {
    id: 1,
    title: "Total Tasks",
    count: caseData.tasks?.length || 0,
    icon: CheckCircle2,
    bg: "bg-blue-50 dark:bg-blue-950",
    text: "text-blue-600 dark:text-blue-400",
    iconBg: "bg-blue-100 dark:bg-blue-900"
  },
  {
    id: 2,
    title: "Total Activities",
    count: caseData.activities?.length || 0,
    icon: Activity,
    bg: "bg-green-50 dark:bg-green-950",
    text: "text-green-600 dark:text-green-400",
    iconBg: "bg-green-100 dark:bg-green-900"
  },
  {
    id: 3,
    title: "Parties Involved",
    count: (caseData.individual_parties?.length || 0) + (caseData.firm_parties?.length || 0),
    icon: Users,
    bg: "bg-purple-50 dark:bg-purple-950",
    text: "text-purple-600 dark:text-purple-400",
    iconBg: "bg-purple-100 dark:bg-purple-900"
  }
])

// Status options for tasks
const statusOptions = [
  { value: 1, label: "Pending", color: "text-yellow-600 bg-yellow-50" },
  { value: 2, label: "In progress", color: "text-blue-600 bg-blue-50" },
  { value: 3, label: "Completed", color: "text-green-600 bg-green-50" },
  { value: 4, label: "Cancelled", color: "text-red-600 bg-red-50" }
]

// Format date function
const formatDate = (dateString?: string) => {
  if (!dateString) return 'Not set'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Description expand/collapse
const isDescriptionExpanded = ref(false)
const maxDescriptionLength = 200

const truncatedDescription = computed(() => {
  if (!caseData.description) return ''
  return caseData.description.length > maxDescriptionLength 
    ? caseData.description.slice(0, maxDescriptionLength) + '...'
    : caseData.description
})

const showDescriptionToggle = computed(() => {
  return caseData.description && caseData.description.length > maxDescriptionLength
})

const toggleDescription = () => {
  isDescriptionExpanded.value = !isDescriptionExpanded.value
}
</script>

<template>
  <div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <Card v-for="stat in statistics" :key="stat.id" class="text-center">
        <CardContent class="pt-6">
          <div class="flex flex-col items-center space-y-3">
            <div :class="[stat.iconBg, 'p-3 rounded-full']">
              <component :is="stat.icon" :class="[stat.text, 'h-6 w-6']" />
            </div>
            <div>
              <p class="text-2xl font-bold text-foreground">{{ stat.count }}</p>
              <p class="text-sm text-muted-foreground mt-1">{{ stat.title }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Case Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- About Case -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="h-5 w-5" />
              Case Details
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Case Title</h3>
              <p class="text-foreground">{{ caseData.title }}</p>
            </div>
            
            <div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Case Number</h3>
              <p class="text-foreground font-mono">#{{ caseData.case_number }}</p>
            </div>
            
            <div v-if="caseData.description">
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Description</h3>
              <p class="text-foreground">
                {{ isDescriptionExpanded ? caseData.description : truncatedDescription }}
              </p>
              <Button 
                v-if="showDescriptionToggle" 
                variant="link" 
                class="p-0 h-auto text-sm" 
                @click="toggleDescription"
              >
                {{ isDescriptionExpanded ? 'Show less' : 'Show more' }}
              </Button>
            </div>

            <!-- Case Status -->
            <div class="flex items-center gap-4 pt-2">
              <div>
                <h3 class="text-sm font-medium text-muted-foreground mb-1">Type</h3>
                <Badge variant="secondary">
                  {{ caseData.type?.name || 'Unknown Type' }}
                </Badge>
              </div>
              <div>
                <h3 class="text-sm font-medium text-muted-foreground mb-1">Stage</h3>
                <Badge variant="outline">
                  {{ caseData.stage?.name || 'No Stage' }}
                </Badge>
              </div>
            </div>

            <!-- Timeline Dates -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 bg-muted/50 rounded-lg p-4">
              <div>
                <h3 class="text-xs font-medium text-muted-foreground mb-1">Date Received</h3>
                <p class="text-sm text-foreground">{{ formatDate(caseData.date_received) }}</p>
              </div>
              <div>
                <h3 class="text-xs font-medium text-muted-foreground mb-1">Date of Filing</h3>
                <p class="text-sm text-foreground">{{ formatDate(caseData.date_of_filing) }}</p>
              </div>
              <div>
                <h3 class="text-xs font-medium text-muted-foreground mb-1">Completion Date</h3>
                <p class="text-sm text-foreground">{{ formatDate(caseData.completion_date) }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Parties Summary -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Users class="h-5 w-5" />
              Parties Summary
            </CardTitle>
            <CardDescription>
              {{ (caseData.individual_parties?.length || 0) + (caseData.firm_parties?.length || 0) }} total parties involved
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-muted-foreground mb-2">Individuals</h3>
                <div class="space-y-2">
                  <div 
                    v-for="party in caseData.individual_parties?.slice(0, 3)" 
                    :key="party.id"
                    class="flex items-center justify-between p-2 rounded-lg bg-muted/50"
                  >
                    <span class="text-sm">
                      {{ party.party?.first_name }} {{ party.party?.last_name }}
                    </span>
                    <Badge variant="outline" class="text-xs">
                      {{ party.party_type?.name || 'Party' }}
                    </Badge>
                  </div>
                  <div 
                    v-if="(caseData.individual_parties?.length || 0) > 3"
                    class="text-center text-sm text-muted-foreground pt-2"
                  >
                    +{{ (caseData.individual_parties?.length || 0) - 3 }} more individuals
                  </div>
                </div>
              </div>
              
              <div>
                <h3 class="text-sm font-medium text-muted-foreground mb-2">Firms</h3>
                <div class="space-y-2">
                  <div 
                    v-for="firm in caseData.firm_parties?.slice(0, 3)" 
                    :key="firm.id"
                    class="flex items-center justify-between p-2 rounded-lg bg-muted/50"
                  >
                    <span class="text-sm">
                      {{ firm.party?.name }}
                    </span>
                    <Badge variant="outline" class="text-xs">
                      {{ firm.party_type?.name || 'Firm' }}
                    </Badge>
                  </div>
                  <div 
                    v-if="(caseData.firm_parties?.length || 0) > 3"
                    class="text-center text-sm text-muted-foreground pt-2"
                  >
                    +{{ (caseData.firm_parties?.length || 0) - 3 }} more firms
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Recent Notes -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="h-5 w-5" />
              Recent Notes
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div 
                v-for="note in caseData.notes?.slice(0, 3)" 
                :key="note.id"
                class="border-l-4 border-primary pl-4 py-2"
              >
                <h4 class="text-sm font-medium text-foreground mb-1">
                  {{ note.title }}
                </h4>
                <p class="text-sm text-muted-foreground mb-2 line-clamp-2">
                  {{ note.note }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ formatDate(note.date) }}
                </p>
              </div>
              <div 
                v-if="!caseData.notes?.length"
                class="text-center text-muted-foreground py-4"
              >
                <FileText class="h-8 w-8 mx-auto mb-2 opacity-50" />
                <p class="text-sm">No notes yet</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Upcoming Tasks -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <CheckCircle2 class="h-5 w-5" />
              Recent Tasks
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div 
                v-for="task in caseData.tasks?.slice(0, 4)" 
                :key="task.id"
                class="flex items-center justify-between p-3 rounded-lg border"
              >
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-foreground truncate">
                    {{ task.title }}
                  </p>
                  <Badge 
                    v-if="statusOptions.find(s => s.value === task.status)"
                    :class="statusOptions.find(s => s.value === task.status)?.color"
                    class="text-xs"
                  >
                    {{ statusOptions.find(s => s.value === task.status)?.label }}
                  </Badge>
                </div>
                <Clock class="h-4 w-4 text-muted-foreground flex-shrink-0 ml-2" />
              </div>
              <div 
                v-if="!caseData.tasks?.length"
                class="text-center text-muted-foreground py-4"
              >
                <CheckCircle2 class="h-8 w-8 mx-auto mb-2 opacity-50" />
                <p class="text-sm">No tasks yet</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Activity -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Activity class="h-5 w-5" />
              Recent Activity
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div 
                v-for="activity in caseData.activities?.slice(0, 3)" 
                :key="activity.id"
                class="flex items-start gap-3"
              >
                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-primary mt-2"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-foreground">
                    {{ activity.title }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ formatDate(activity.date) }}
                  </p>
                </div>
              </div>
              <div 
                v-if="!caseData.activities?.length"
                class="text-center text-muted-foreground py-4"
              >
                <Activity class="h-8 w-8 mx-auto mb-2 opacity-50" />
                <p class="text-sm">No recent activity</p>
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