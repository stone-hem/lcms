<!-- resources/js/components/legal-cases/modals/EditCaseModal.vue -->
<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import Select from '@/components/form/Select.vue'
import MultiSelect from '@/components/form/MultiSelect.vue'
import DatePicker from '@/components/form/DatePicker.vue'

const props = defineProps<{
  open: boolean
  legalCase?: any,
  presets: any
}>()

const emit = defineEmits(['close', 'success'])

const isInternal = ref(true)

const form = useForm({
  title: '',
  case_number: '',
  court_name: '',
  description: '',
  status: '',
  date_received: '',
  date_filed: '',
  case_type_id: '',
  nature_of_claim_id: '',
  lawyer_id: [],
  external_advocate_ids: [],
  contigent_liability: null,
  mention_date: '',
  determination_date: ''
})



watch(() => props.open, (open) => {
  if (open && props.legalCase) {
    fillForm()
  }
}, { immediate: true })

const fillForm = () => {
  if (!props.legalCase) return

  form.reset()
  form.title = props.legalCase.title
  form.case_number = props.legalCase.case_number
  form.court_name = props.legalCase.court_name
  form.description = props.legalCase.description || ''
  form.status = props.legalCase.status?.id || props.legalCase.status
  form.date_received = props.legalCase.date_received
  form.date_filed = props.legalCase.date_filed
  form.case_type_id = props.legalCase.case_type_id
  form.contigent_liability = props.legalCase.contigent_liability
  form.mention_date = props.legalCase.mention_date
  form.determination_date = props.legalCase.determination_date

  isInternal.value = props.legalCase.is_internal
  form.lawyer_id = props.legalCase.lawyers?.map((l: any) => l.id) || []
  form.external_advocate_ids = props.legalCase.external_advocates?.map((l: any) => l.id) || []
}

const submit = () => {
  form.is_internal = isInternal.value
  if (!isInternal.value) form.lawyer_id = []
  if (isInternal.value) form.external_advocate_ids = []

  form.put(`/legal_cases/${props.legalCase.id}`, {
    onSuccess: () => emit('success'),
    preserveScroll: true
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Legal Case</DialogTitle>
        <DialogDescription>Update case details.</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6" v-if="props.legalCase">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Basic Info -->
            <div>
              <Label for="title">Case Title <span class="text-red-600">*</span></Label>
              <Input id="title" v-model="form.title" required placeholder="Enter case title" />
              <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
            </div>
  
            <div>
              <Label for="case_number">Case Number <span class="text-red-600">*</span></Label>
              <Input id="case_number" v-model="form.case_number" required placeholder="e.g. CIV/2025/001" />
              <p v-if="form.errors.case_number" class="text-sm text-red-600 mt-1">{{ form.errors.case_number }}</p>
            </div>
  
            <div>
              <Label for="court_name">Court Name <span class="text-red-600">*</span></Label>
              <Input id="court_name" v-model="form.court_name" required placeholder="High Court of Kenya" />
            </div>
  
            <div>
              <Label for="case_type">Case Type <span class="text-red-600">*</span></Label>
              <Select v-model="form.case_type_id" required>
                <option value="">Select case type</option>
                <option v-for="type in presets.case_types" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
              </Select>
            </div>
  
            <div>
              <Label for="date_received">Date Received <span class="text-red-600">*</span></Label>
              <DatePicker v-model="form.date_received" required />
            </div>
  
            <div>
              <Label for="date_filed">Date Filed <span class="text-red-600">*</span></Label>
              <DatePicker v-model="form.date_filed" required />
            </div>
  
            <div>
              <Label for="status">Status <span class="text-red-600">*</span></Label>
              <Select v-model="form.status" required>
                <option v-for="s in presets.statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
              </Select>
            </div>
  
            <div>
              <Label for="contigent_liability">Contingent Liability (KES)</Label>
              <Input type="number" step="0.01" v-model.number="form.contigent_liability" placeholder="0.00" />
            </div>
          </div>
  
          <div>
            <Label>Description</Label>
            <TextArea v-model="form.description" rows="3" placeholder="Brief description of the case..." />
          </div>
  
          <!-- Counsel Type -->
          <div class="space-y-3">
            <Label>Counsel Type <span class="text-red-600">*</span></Label>
            <div class="flex gap-6">
              <label class="flex items-center gap-2">
                <input type="radio" v-model="isInternal" :value="true" class="radio" />
                <span>Internal Lawyer</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" v-model="isInternal" :value="false" class="radio" />
                <span>External Advocate</span>
              </label>
            </div>
          </div>
  
          <div v-if="isInternal">
            <Label>Assigned Internal Lawyer(s)</Label>
            <MultiSelect returnIdsOnly v-model="form.lawyer_id" :options="presets.lawyers" placeholder="Select lawyers" />
          </div>
  
          <div v-else>
            <Label>External Advocate(s)</Label>
            <MultiSelect returnIdsOnly v-model="form.external_advocate_ids" :options="presets.external_advocates" placeholder="Select external advocates" />
          </div>
  
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Mention Date</Label>
              <DatePicker v-model="form.mention_date" />
            </div>
            <div>
              <Label>Determination Date</Label>
              <DatePicker v-model="form.determination_date" />
            </div>
          </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Updating...' : 'Update Case' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>