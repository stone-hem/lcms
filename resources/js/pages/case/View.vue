<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft } from 'lucide-vue-next'

import CaseOverviewTab from '@/components/cases/tabs/CaseOverviewTab.vue'
import CasePartiesAndFirmsTab from '@/components/cases/parties/CasePartiesAndFirmsTab.vue'
import CaseActivityTab from '@/components/cases/activities/CaseActivityTab.vue'
import CaseTasksTab from '@/components/cases/tasks/CaseTasksTab.vue'
import CaseNotesTab from '@/components/cases/notes/CaseNotesTab.vue'
import CaseAttachmentsTab from '@/components/attachments/CaseAttachmentsTab.vue'
import CaseCalendarTab from '@/components/cases/calendar/CaseCalendarTab.vue'

const props = defineProps<{
  case: any,
  partyTypes: any[]
}>()

const caseData = props.case

const tabs = [
  { id: 'overview', name: 'Case Overview' },
  { id: 'parties', name: 'Parties & Firms' },
  { id: 'activity', name: 'Activity' },
  { id: 'tasks', name: 'Tasks' },
  { id: 'notes', name: 'Notes' },
  { id: 'documents', name: 'Documents' },
  { id: 'calendar', name: 'Calendar' },
]

const activeTab = ref('overview')
</script>


<template>
  <AppLayout>
    <Head :title="`${caseData.title} #${caseData.case_number}`" />
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="icon" @click="router.back()">
            <ArrowLeft class="h-5 w-5" />
          </Button>
          <div>
            <h1 class="text-3xl font-bold text-foreground">
              {{ caseData.title }}
            </h1>
            <div class="flex items-center gap-3 mt-1">
              <Badge variant="secondary" class="font-mono">
                #{{ caseData.case_number }}
              </Badge>
              <span class="text-sm text-muted-foreground">
                {{ caseData.case_type?.name || 'Unknown Type' }}
                • {{ caseData.case_stage?.name || 'No Stage' }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Button variant="outline">Close Case</Button>
          <Button>Edit Case</Button>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="border-b">
        <nav class="flex space-x-8 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-primary text-primary font-medium'
                : 'border-transparent text-muted-foreground hover:text-foreground',
              'whitespace-nowrap py-4 px-1 border-b-2 text-sm font-medium transition-colors duration-200'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="space-y-6">
        <!-- Overview -->
        <div v-show="activeTab === 'overview'">
          <Card>
            <CaseOverviewTab :case="caseData" />
          </Card>
        </div>

        <!-- Parties & Firms -->
        <div v-show="activeTab === 'parties'">
          <Card>
            <CasePartiesAndFirmsTab :partyTypes="partyTypes" :legalCaseId="caseData.id" :parties="caseData.individual_parties" :firms="caseData.firm_parties" />
          </Card>
        </div>

        <!-- Activity -->
        <div v-show="activeTab === 'activity'">
          <Card>
            <CaseActivityTab :caseActivityTypes="caseData.case_activity_types" :participants="caseData.lawyers" :legalCaseId="caseData.id" :activities="caseData.activities" />
          </Card>
        </div>

        <!-- Tasks -->
        <div v-show="activeTab === 'tasks'">
          <Card>
            <CaseTasksTab :tasks="caseData.tasks" />
          </Card>
        </div>

        <!-- Notes -->
        <div v-show="activeTab === 'notes'">
          <Card>
            <CaseNotesTab :notes="caseData.notes" />
          </Card>
        </div>

        <!-- Documents -->
        <div v-show="activeTab === 'documents'">
          <Card>
            <CaseAttachmentsTab :attachments="caseData.attachments" />
          </Card>
        </div>

        <!-- Calendar -->
        <div v-show="activeTab === 'calendar'">
          <Card>
            <CaseCalendarTab :events="caseData.events" :tasks="caseData.tasks" :activities="[]" />
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Optional: smooth tab transitions */
div[v-show] {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>