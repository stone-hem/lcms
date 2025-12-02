<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import TextArea from '@/components/form/TextArea.vue'
import Select from '@/components/form/Select.vue'
import MultiSelect from '@/components/form/MultiSelect.vue'
import DatePicker from '@/components/form/DatePicker.vue'
import DisplayErrors from '@/components/ui/DisplayErrors.vue'

const props = defineProps<{ open: boolean, presets: any }>()
const emit = defineEmits(['close', 'success'])

const isInternal = ref(true)
const showInterimFeeFields = ref(false)
const showFinalFeeFields = ref(false)

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
  is_internal: true,
  lawyer_id: [],
  external_advocate_ids: [],
  contigent_liability: null,
  mention_date: '',
  determination_date: '',
  // SLA Fields
  sla_agreed_amount: null as number | null,
  sla_paid_amount: null as number | null,
  sla_balance: null as number | null,
  // Interim Fee Note
  interim_fee_note_amount: null as number | null,
  interim_fee_note_is_paid: false,
  interim_fee_note_amount_paid: null as number | null,
  interim_fee_note_balance: null as number | null,
  // Final Fee Note
  final_fee_note_amount: null as number | null,
  final_fee_note_is_paid: false,
  final_fee_note_amount_paid: null as number | null,
  final_fee_note_balance: null as number | null,
})

// Computed for SLA balance
const slaBalance = computed(() => {
  const agreed = parseFloat(form.sla_agreed_amount?.toString() || '0')
  const paid = parseFloat(form.sla_paid_amount?.toString() || '0')
  return agreed - paid
})

// Computed for interim fee balance
const interimFeeBalance = computed(() => {
  const amount = parseFloat(form.interim_fee_note_amount?.toString() || '0')
  const paid = parseFloat(form.interim_fee_note_amount_paid?.toString() || '0')
  return amount - paid
})

// Computed for final fee balance
const finalFeeBalance = computed(() => {
  const amount = parseFloat(form.final_fee_note_amount?.toString() || '0')
  const paid = parseFloat(form.final_fee_note_amount_paid?.toString() || '0')
  return amount - paid
})

// Watch for changes to update balances
watch(() => [form.sla_agreed_amount, form.sla_paid_amount], () => {
  form.sla_balance = slaBalance.value
})

watch(() => [form.interim_fee_note_amount, form.interim_fee_note_amount_paid], () => {
  form.interim_fee_note_balance = interimFeeBalance.value
})

watch(() => [form.final_fee_note_amount, form.final_fee_note_amount_paid], () => {
  form.final_fee_note_balance = finalFeeBalance.value
})

// Watch for payment status changes
watch(() => form.interim_fee_note_is_paid, (isPaid) => {
  if (isPaid && form.interim_fee_note_amount) {
    form.interim_fee_note_amount_paid = form.interim_fee_note_amount
  } else if (!isPaid) {
    form.interim_fee_note_amount_paid = 0
  }
})

watch(() => form.final_fee_note_is_paid, (isPaid) => {
  if (isPaid && form.final_fee_note_amount) {
    form.final_fee_note_amount_paid = form.final_fee_note_amount
  } else if (!isPaid) {
    form.final_fee_note_amount_paid = 0
  }
})

const submit = () => {
  form.is_internal = isInternal.value
  if (!isInternal.value) form.lawyer_id = []
  if (isInternal.value) form.external_advocate_ids = []
  
  // Set balances before submitting
  form.sla_balance = slaBalance.value
  form.interim_fee_note_balance = interimFeeBalance.value
  form.final_fee_note_balance = finalFeeBalance.value

  form.post('/legal_cases', {
    onSuccess: () => {
      form.reset()
      emit('success')
    },
    preserveScroll: true
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')" class="w-7xl">
    <DialogContent class="max-w-5xl max-h-[95vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add New Legal Case</DialogTitle>
        <DialogDescription>Create a new legal case with full details.</DialogDescription>
      </DialogHeader>
      
      <DisplayErrors :errors="$page.props.errors" />


      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
        </div>

        <!-- Counsel Type Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">Legal Representation</h3>
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
        </div>

        <!-- SLA Section -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">SLA Information</h3>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <Label for="sla_agreed_amount">SLA Agreed Amount (KES)</Label>
              <Input 
                type="number" 
                step="0.01" 
                id="sla_agreed_amount" 
                v-model.number="form.sla_agreed_amount" 
                placeholder="0.00" 
              />
            </div>

            <div>
              <Label for="sla_paid_amount">SLA Paid Amount (KES)</Label>
              <Input 
                type="number" 
                step="0.01" 
                id="sla_paid_amount" 
                v-model.number="form.sla_paid_amount" 
                placeholder="0.00" 
              />
            </div>

            <div>
              <Label for="sla_balance">SLA Balance (KES)</Label>
              <Input 
                type="number" 
                step="0.01" 
                id="sla_balance" 
                :value="slaBalance" 
                disabled
                class="bg-gray-50"
                placeholder="0.00" 
              />
            </div>
          </div>
        </div>

        <!-- Interim Fee Note Section -->
        <div class="space-y-4 border-t pt-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Checkbox v-model="showInterimFeeFields" id="show_interim" />
              <Label for="show_interim" class="cursor-pointer font-medium">Has Interim Fee Note</Label>
            </div>
          </div>

          <div v-if="showInterimFeeFields" class="space-y-4 bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <Label for="interim_fee_note_amount">Interim Fee Amount (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="interim_fee_note_amount" 
                  v-model.number="form.interim_fee_note_amount" 
                  placeholder="0.00" 
                />
              </div>

              <div class="flex items-center space-x-2">
                <Checkbox v-model="form.interim_fee_note_is_paid" id="interim_paid" />
                <Label for="interim_paid" class="cursor-pointer">Interim Fee Paid</Label>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              <div>
                <Label for="interim_fee_note_amount_paid">Amount Paid (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="interim_fee_note_amount_paid" 
                  v-model.number="form.interim_fee_note_amount_paid" 
                  :disabled="form.interim_fee_note_is_paid"
                  placeholder="0.00" 
                />
              </div>

              <div>
                <Label for="interim_fee_note_balance">Balance (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="interim_fee_note_balance" 
                  :value="interimFeeBalance" 
                  disabled
                  class="bg-gray-50"
                  placeholder="0.00" 
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Final Fee Note Section -->
        <div class="space-y-4 border-t pt-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Checkbox v-model="showFinalFeeFields" id="show_final" />
              <Label for="show_final" class="cursor-pointer font-medium">Has Final Fee Note</Label>
            </div>
          </div>

          <div v-if="showFinalFeeFields" class="space-y-4 bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <Label for="final_fee_note_amount">Final Fee Amount (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="final_fee_note_amount" 
                  v-model.number="form.final_fee_note_amount" 
                  placeholder="0.00" 
                />
              </div>

              <div class="flex items-center space-x-2">
                <Checkbox v-model="form.final_fee_note_is_paid" id="final_paid" />
                <Label for="final_paid" class="cursor-pointer">Final Fee Paid</Label>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
              <div>
                <Label for="final_fee_note_amount_paid">Amount Paid (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="final_fee_note_amount_paid" 
                  v-model.number="form.final_fee_note_amount_paid" 
                  :disabled="form.final_fee_note_is_paid"
                  placeholder="0.00" 
                />
              </div>

              <div>
                <Label for="final_fee_note_balance">Balance (KES)</Label>
                <Input 
                  type="number" 
                  step="0.01" 
                  id="final_fee_note_balance" 
                  :value="finalFeeBalance" 
                  disabled
                  class="bg-gray-50"
                  placeholder="0.00" 
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Important Dates Section -->
        <div class="space-y-4 border-t pt-4">
          <h3 class="text-lg font-semibold text-gray-900">Important Dates</h3>
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
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create Case' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>