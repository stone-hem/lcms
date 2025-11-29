<script setup lang="ts">
import { watch } from 'vue'
import Select from '@/components/form/Select.vue';
import { Button } from '@/components/ui/button'
import {
  Dialog, DialogContent, DialogDescription, DialogFooter,
  DialogHeader, DialogTitle, DialogClose
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  open: boolean
  party?: any
  partyTypes: Array<{ id: number; name: string }>
}>()

const emit = defineEmits(['close', 'success'])

const location = ref({
  address_line: '',
  postal_code: '',
  postal_address: '',
  town: '',
})

const form = useForm({
  id: null,
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  location: {} as any,
  party_type_id: '',
  legal_case_id: props.party?.legal_case_id,
})

watch(() => props.party, (p) => {
  if (p && props.open) {
    form.id = p.id
    form.first_name = p.party?.first_name || ''
    form.middle_name = p.party?.middle_name || ''
    form.last_name = p.party?.last_name || ''
    form.email = p.party?.email || ''
    form.phone = p.party?.phone || ''
    form.date_of_birth = p.party?.date_of_birth || ''
    form.gender = p.party?.gender || ''
    form.party_type_id = p.party_type_id?.toString() || ''
    form.legal_case_id = p.legal_case_id,

    location.value = {
      address_line: p.party?.location?.address_line || '',
      town: p.party?.location?.town || '',
      postal_code: p.party?.location?.postal_code || '',
      postal_address: p.party?.location?.postal_address || '',
    }
  }
}, { immediate: true })

const submit = () => {
  form.location = location.value
  form.put('/parties/individual', {
    onSuccess: () => emit('success'),
    onFinish: () => emit('close'),
  })
}
</script>

<template>
<Dialog :open="open" @update:open="emit('close')">
  <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
    <DialogHeader>
      <DialogTitle>Edit Individual Party</DialogTitle>
      <DialogDescription>Edit a person involved in this case</DialogDescription>
    </DialogHeader>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label>First Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.first_name" required placeholder="John" />
        </div>
        <div>
          <Label>Middle Name</Label>
          <Input v-model="form.middle_name" placeholder="Michael" />
        </div>
        <div>
          <Label>Last Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.last_name" required placeholder="Doe" />
        </div>
        <div>
          <Label>Email</Label>
          <Input v-model="form.email" type="email" placeholder="john@example.com" />
        </div>
        <div>
          <Label>Phone</Label>
          <Input v-model="form.phone" placeholder="712345678" />
        </div>
        <div>
          <Label>Date of Birth</Label>
          <Input v-model="form.date_of_birth" type="date" />
        </div>
        <div>
          <Label>Gender</Label>
          <Select v-model="form.gender">
              <option placeholder="Select gender" />
              <option value="male">Male</option>
              <option value="female">Female</option>
          </Select>
        </div>
        <div>
          <Label>Party Type <span class="text-red-600">*</span></Label>
          <Select v-model="form.party_type_id" required>
              <option placeholder="Select type" />
                <option v-for="type in partyTypes" :key="type.id" :value="type.id">
                  {{ type.name }}
                </option>
            </Select>
        </div>
      </div>

      <div class="space-y-3 border-t pt-4">
        <h4 class="font-medium">Location Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <Label>Address Line</Label>
            <Input v-model="location.address_line" placeholder="123 Main Street" />
          </div>
          <div>
            <Label>Town/City</Label>
            <Input v-model="location.town" placeholder="Nairobi" />
          </div>
          <div>
            <Label>Postal Code</Label>
            <Input v-model="location.postal_code" placeholder="00100" />
          </div>
          <div class="md:col-span-2">
            <Label>Postal Address</Label>
            <Input v-model="location.postal_address" placeholder="P.O. Box 12345-00100 Nairobi" />
          </div>
        </div>
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button type="button" variant="outline">Cancel</Button>
        </DialogClose>
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Editing...' : 'Add Individual' }}
        </Button>
      </DialogFooter>
    </form>
  </DialogContent>
</Dialog>
</template>