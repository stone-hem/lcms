<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'
import CaseTypesSection from '@/components/settings/misc/CaseTypesSection.vue'
import NatureOfClaimsSection from '@/components/settings/misc/NatureOfClaimsSection.vue'
import PartyTypesSection from '@/components/settings/misc/PartyTypesSection.vue'
import CaseActivitiesSection from '@/components/settings/misc/CaseActivitiesSection.vue'
import DocumentTypesSection from '@/components/settings/misc/DocumentTypesSection.vue'
import CaseStagesSection from '@/components/settings/misc/CaseStagesSection.vue'
import EventCategoriesSection from '@/components/settings/misc/EventCategoriesSection.vue'
import ExpenseTypesSection from '@/components/settings/misc/ExpenseTypesSection.vue'
import { ref } from 'vue'

defineProps({
  misc: Object,
  success: String
})

const tabs = [
  { id: 'case_types', name: 'Case Types' },
  { id: 'nature_of_claims', name: 'Nature of Claim' },
  { id: 'party_types', name: 'Party Types' },
  { id: 'case_activities', name: 'Case Activities' },
  { id: 'document_types', name: 'Document Types' },
  { id: 'case_stages', name: 'Case Stages' },
  { id: 'event_categories', name: 'Event Categories' },
  { id: 'expense_types', name: 'Expense Types' },
]

let activeTab = ref('case_types')
</script>

<template>
  <AppLayout>
    <Head title="Miscellaneous Settings" />

    <div class="min-h-screen bg-gray-50 dark:bg-black md:p-8 p-4">
      <div class=" mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <h1 class="text-3xl font-bold text-gray-900 mb-8 dark:text-white">Miscellaneous Settings</h1>

        <!-- Success Message -->
        <div v-if="success" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ success }}
        </div>

        <!-- Tabs -->
        <div class=" mb-8">
          <nav class="-mb-px flex space-x-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div v-show="activeTab === 'case_types'">
          <CaseTypesSection :items="misc.case_types" />
        </div>
        <div v-show="activeTab === 'nature_of_claims'">
          <NatureOfClaimsSection :items="misc.nature_of_claims" />
        </div>
        <div v-show="activeTab === 'party_types'">
          <PartyTypesSection :items="misc.party_types" />
        </div>
        <div v-show="activeTab === 'case_activities'">
          <CaseActivitiesSection :items="misc.case_activities" />
        </div>
        <div v-show="activeTab === 'document_types'">
          <DocumentTypesSection :items="misc.document_types" />
        </div>
        <div v-show="activeTab === 'case_stages'">
          <CaseStagesSection :items="misc.case_stages" />
        </div>
        <div v-show="activeTab === 'event_categories'">
          <EventCategoriesSection :items="misc.event_categories" />
        </div>
        <div v-show="activeTab === 'expense_types'">
          <ExpenseTypesSection :items="misc.expense_types" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>