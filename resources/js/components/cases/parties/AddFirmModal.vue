<script setup lang="ts">
import { Button } from '@/components/ui/button'
import {
  Dialog, DialogContent, DialogFooter,
  DialogHeader, DialogTitle, DialogClose
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Select from '@/components/form/Select.vue';
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps<{
  open: boolean
  legalCaseId: number
  partyTypes: Array<{ id: number; name: string }>
}>()

const location = ref({
  address_line: '',
  postal_code: '',
  postal_address: '',
  town: '',
})

const form = useForm({
  name: '',
  email: '',
  phone: '',
  location: {} as any,
  party_type_id: '',
  legal_case_id: props.legalCaseId,
})

const submit = () => {
  form.location = location.value
  form.post('/parties/firm', {
    onSuccess: () => {
      form.reset()
      location.value = { address_line: '', postal_code: '', postal_address: '', town: '' }
      emit('success')
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Add Firm / Company</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <Label>Company Name <span class="text-red-600">*</span></Label>
          <Input v-model="form.name" required placeholder="ABC Company Limited" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Email</Label>
            <Input v-model="form.email" type="email" />
          </div>
          <div>
            <Label>Phone</Label>
            <Input v-model="form.phone" />
          </div>
          <div>
            <Label>Party Type <span class="text-red-600">*</span></Label>
            <Select v-model="form.party_type_id" required>
              <option>e.g. Plaintiff Company</option>
                <option v-for="t in partyTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
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
          <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
          <Button type="submit">Add Firm</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>