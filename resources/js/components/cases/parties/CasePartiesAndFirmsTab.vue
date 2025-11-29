<script setup lang="ts">
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { 
  Users, 
  Building, 
  Plus, 
  Search, 
  Edit, 
  Trash2, 
  User,
  Mail,
  Phone,
  MapPin
} from 'lucide-vue-next'
import AddIndividualModal from '@/components/cases/parties/AddIndividualModal.vue'
import EditIndividualModal from '@/components/cases/parties/EditIndividualModal.vue'
import DeleteIndividualModal from '@/components/cases/parties/DeleteIndividualModal.vue'
import AddFirmModal from '@/components/cases/parties/AddFirmModal.vue'
import EditFirmModal from '@/components/cases/parties/EditFirmModal.vue'
import DeleteFirmModal from '@/components/cases/parties/DeleteFirmModal.vue'

const props = defineProps<{
  parties?: any[]
  firms?: any[]
  legalCaseId: number,
  partyTypes: any[]
}>()

const searchQuery = ref('')

// Filter parties and firms based on search
const filteredParties = computed(() => {
  if (!props.parties) return []
  if (!searchQuery.value) return props.parties
  
  return props.parties.filter(party => 
    party.party?.first_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    party.party?.last_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    party.party?.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    party.party_type?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const filteredFirms = computed(() => {
  if (!props.firms) return []
  if (!searchQuery.value) return props.firms
  
  return props.firms.filter(firm =>
    firm.party?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    firm.party?.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    firm.party_type?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const modals = {
  addIndividual: ref(false),
  editIndividual: ref(false),
  deleteIndividual: ref(false),
  addFirm: ref(false),
  editFirm: ref(false),
  deleteFirm: ref(false),
}

const selected = {
  individual: ref(null),
  firm: ref(null),
}
// Format phone number for display
const formatPhone = (phone: string) => {
  if (!phone) return 'Not provided'
  return phone
}

// Format address for display
const formatAddress = (address: string) => {
  if (!address) return 'Not provided'
  return address.length > 30 ? address.substring(0, 30) + '...' : address
}

const reload = () => router.reload({ only: ['parties', 'firms'] })
</script>

<template>
  <div class="space-y-6">
    <!-- Search and Add Buttons -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="searchQuery"
          placeholder="Search parties or firms..."
          class="pl-10"
        />
      </div>
      <div class="flex gap-2">
        <Button @click="modals.addIndividual.value = true" class="flex items-center gap-2">
          <User class="h-4 w-4" />
          Add Party
        </Button>
        <Button @click="modals.addFirm.value = true" class="flex items-center gap-2">
          <Building class="h-4 w-4" />
          Add Firm
        </Button>
      </div>
    </div>

    <!-- Parties Section -->
    <Card>
      <CardHeader class="pb-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Users class="h-5 w-5 text-muted-foreground" />
            <CardTitle>Parties</CardTitle>
          </div>
          <Badge variant="secondary" class="text-sm">
            {{ parties?.length || 0 }} {{ (parties?.length || 0) === 1 ? 'party' : 'parties' }}
          </Badge>
        </div>
        <CardDescription>
          Individuals involved in the case
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="!parties?.length" class="text-center py-8 text-muted-foreground">
          <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
          <p>No parties added yet</p>
          <Button @click="modals.addIndividual.value = true" variant="outline" class="mt-4">
            <Plus class="h-4 w-4 mr-2" />
            Add First Party
          </Button>
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="party in filteredParties" 
            :key="party.id"
            class="flex items-start justify-between p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors"
          >
            <div class="flex-1 space-y-3">
              <!-- Header with name and party type -->
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-medium text-foreground">
                    {{ party.party?.first_name }} 
                    {{ party.party?.middle_name ? party.party.middle_name + ' ' : '' }}
                    {{ party.party?.last_name }}
                  </h3>
                  <Badge v-if="party.party_type" variant="outline" class="mt-1 text-xs">
                    {{ party.party_type.name }}
                  </Badge>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-2">
                  <div v-if="party.party?.email" class="flex items-center gap-2 text-muted-foreground">
                    <Mail class="h-4 w-4" />
                    <span class="truncate">{{ party.party.email }}</span>
                  </div>
                  <div v-if="party.party?.phone" class="flex items-center gap-2 text-muted-foreground">
                    <Phone class="h-4 w-4" />
                    <span>{{ formatPhone(party.party.phone) }}</span>
                  </div>
                </div>
                <div class="space-y-2">
                  <div v-if="party.party?.physical_address" class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span class="truncate">{{ formatAddress(party.party.physical_address) }}</span>
                  </div>
                  <div v-if="party.party?.postal_address" class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span class="truncate">{{ formatAddress(party.party.postal_address) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-4">
              <Button 
              @click="selected.individual = party; modals.editIndividual.value = true"
                variant="ghost" 
                size="icon"
                class="h-8 w-8"
              >
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
                @click="selected.individual = party; modals.deleteIndividual.value = true" 
                variant="ghost" 
                size="icon"
                class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>

          <div 
            v-if="filteredParties.length === 0 && parties.length > 0"
            class="text-center py-8 text-muted-foreground"
          >
            <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No parties match your search</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Firms Section -->
    <Card>
      <CardHeader class="pb-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Building class="h-5 w-5 text-muted-foreground" />
            <CardTitle>Firms</CardTitle>
          </div>
          <Badge variant="secondary" class="text-sm">
            {{ firms?.length || 0 }} {{ (firms?.length || 0) === 1 ? 'firm' : 'firms' }}
          </Badge>
        </div>
        <CardDescription>
          Organizations and companies involved in the case
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="!firms?.length" class="text-center py-8 text-muted-foreground">
          <Building class="h-12 w-12 mx-auto mb-4 opacity-50" />
          <p>No firms added yet</p>
          <Button @click="modals.addFirm.value = true" variant="outline" class="mt-4">
            <Plus class="h-4 w-4 mr-2" />
            Add First Firm
          </Button>
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="firm in filteredFirms" 
            :key="firm.id"
            class="flex items-start justify-between p-4 rounded-lg border bg-card hover:bg-muted/50 transition-colors"
          >
            <div class="flex-1 space-y-3">
              <!-- Header with firm name and party type -->
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-medium text-foreground">
                    {{ firm.party?.name }}
                  </h3>
                  <Badge v-if="firm.party_type" variant="outline" class="mt-1 text-xs">
                    {{ firm.party_type.name }}
                  </Badge>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-2">
                  <div v-if="firm.party?.email" class="flex items-center gap-2 text-muted-foreground">
                    <Mail class="h-4 w-4" />
                    <span class="truncate">{{ firm.party.email }}</span>
                  </div>
                  <div v-if="firm.party?.phone" class="flex items-center gap-2 text-muted-foreground">
                    <Phone class="h-4 w-4" />
                    <span>{{ formatPhone(firm.party.phone) }}</span>
                  </div>
                </div>
                <div class="space-y-2">
                  <div v-if="firm.party?.physical_address" class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span class="truncate">{{ formatAddress(firm.party.physical_address) }}</span>
                  </div>
                  <div v-if="firm.party?.postal_address" class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span class="truncate">{{ formatAddress(firm.party.postal_address) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-4">
              <Button 
              @click="selected.firm = firm; modals.editFirm.value = true"
                variant="ghost" 
                size="icon"
                class="h-8 w-8"
              >
                <Edit class="h-4 w-4" />
              </Button>
              <Button 
              @click="selected.firm = firm; modals.deleteFirm.value = true"
                variant="ghost" 
                size="icon"
                class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>

          <div 
            v-if="filteredFirms.length === 0 && firms.length > 0"
            class="text-center py-8 text-muted-foreground"
          >
            <Search class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No firms match your search</p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
  
  <AddIndividualModal
      :open="modals.addIndividual.value"
      :legalCaseId="legalCaseId"
      :partyTypes="partyTypes"
      @close="modals.addIndividual.value = false"
      @success="reload"
    />
  
    <EditIndividualModal
      :open="modals.editIndividual.value"
      :party="selected.individual"
      :partyTypes="partyTypes"
      @close="modals.editIndividual.value = false"
      @success="reload"
    />
  
    <DeleteIndividualModal
      :open="modals.deleteIndividual.value"
      :party="selected.individual"
      @close="modals.deleteIndividual.value = false"
      @deleted="reload"
    />
  
    <AddFirmModal
      :open="modals.addFirm.value"
      :legalCaseId="legalCaseId"
      :partyTypes="partyTypes"
      @close="modals.addFirm.value = false"
      @success="reload"
    />
  
    <EditFirmModal
      :open="modals.editFirm.value"
      :firm="selected.firm"
      :party-types="partyTypes"
      @close="modals.editFirm.value = false"
      @success="reload"
    />
  
    <DeleteFirmModal
      :open="modals.deleteFirm.value"
      :firm="selected.firm"
      @close="modals.deleteFirm.value = false"
      @deleted="reload"
    />
</template>

<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>