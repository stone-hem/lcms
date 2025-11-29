<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Select from '@/components/form/Select.vue';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps<{
  open: boolean
  firm?: any // the full firm object from your list
  partyTypes: Array<{ id: number; name: string }>
}>()

const emit = defineEmits(['close', 'success'])

// Separate ref for location object (so we can mutate it easily)
const location = ref({
  address_line: '',
  town: '',
  postal_code: '',
  postal_address: '',
})

const form = useForm({
  id: null as number | null,
  name: '',
  email: '',
  phone: '',
  party_type_id: '',
  legal_case_id: null as number | null,
  location: {} as any,
})

// Fill form when modal opens or firm changes
watch(
  () => props.firm,
  (newFirm) => {
    if (newFirm && props.open) {
      form.id = newFirm?.id
      form.name = newFirm?.party?.name || ''
      form.email = newFirm?.party?.email || ''
      form.phone = newFirm?.party?.phone || ''
      form.party_type_id = newFirm?.party_type_id?.toString() || ''
      form.legal_case_id = newFirm?.legal_case_id

      location.value = {
        address_line: newFirm?.party?.location?.address_line || '',
        town: newFirm?.party?.location?.town || '',
        postal_code: newFirm?.party?.location?.postal_code || '',
        postal_address: newFirm?.party?.location?.postal_address || '',
      }
    }
  },
  { immediate: true }
)

const submit = () => {
  form.location = location.value

  form.put('/parties/firm', {
    onSuccess: () => {
      emit('success')
      emit('close')
    },
    preserveScroll: true,
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Firm / Company</DialogTitle>
        <DialogDescription>
          Update details for <strong>{{ firm?.party.name }}</strong>
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6 mt-4">
        <!-- Company Name -->
        <div>
          <Label>Company Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.name" required placeholder="ABC Company Limited" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Email & Phone -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>Email</Label>
            <Input v-model="form.email" type="email" placeholder="info@company.com" />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <Label>Phone</Label>
            <Input v-model="form.phone" placeholder="720123456" />
            <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</p>
          </div>
        </div>

        <!-- Party Type -->
        <div>
          <Label>Party Type <span class="text-red-600">*</span></Label>
          <Select v-model="form.party_type_id" required>
            <option>
              Select party type
            </option>
              <option v-for="type in partyTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
          </Select>
          <p v-if="form.errors.party_type_id" class="text-sm text-red-600 mt-1">
            {{ form.errors.party_type_id }}
          </p>
        </div>

        <!-- Location Section -->
        <div class="border-t pt-6">
          <h4 class="font-medium mb-4">Location Details</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <Label>Address Line</Label>
              <Input v-model="location.address_line" placeholder="789 Business Avenue" />
            </div>
            <div>
              <Label>Town/City</Label>
              <Input v-model="location.town" placeholder="Nairobi" />
            </div>
            <div>
              <Label>Postal Code</Label>
              <Input v-model="location.postal_code" placeholder="00300" />
            </div>
            <div class="md:col-span-2">
              <Label>Postal Address</Label>
              <Input v-model="location.postal_address" placeholder="P.O. Box 54321-00300 Nairobi" />
            </div>
          </div>
        </div>

        <!-- Footer -->
        <DialogFooter class="mt-8">
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Update Firm' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>