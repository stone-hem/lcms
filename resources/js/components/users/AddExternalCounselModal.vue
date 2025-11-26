<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{ open: boolean }>()
const emit = defineEmits(['close', 'success'])

const canLogin = ref(false)
const form = ref({
  firm_name: '',
  bank_name: '',
  bank_branch: '',
  bank_account_number: '',
  postal_address: '',
  kra_pin: '',
  email: '',
  password: '',
})

const submit = async () => {
  try {
    const payload: any = { ...form.value }
    payload.can_login = canLogin.value

    await axios.post('/external_council', payload)
    emit('success')
    emit('close')
    form.value = { firm_name: '', bank_name: '', bank_branch: '', bank_account_number: '', postal_address: '', kra_pin: '', email: '', password: '' }
    canLogin.value = false
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Failed to create external counsel'
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add External Counsel / Law Firm</DialogTitle>
        <DialogDescription>
          Register a new external advocate or law firm.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-5">
          <div class="col-span-2">
            <Label>Firm / Advocate Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.firm_name" required placeholder="e.g. Kimani & Associates" />
          </div>

          <div>
            <Label>Bank Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.bank_name" required />
          </div>
          <div>
            <Label>Bank Branch <span class="text-red-600">*</span></Label>
            <Input v-model="form.bank_branch" required />
          </div>
          <div>
            <Label>Bank Account Number <span class="text-red-600">*</span></Label>
            <Input v-model="form.bank_account_number" required />
          </div>
          <div>
            <Label>Postal Address <span class="text-red-600">*</span></Label>
            <Input v-model="form.postal_address" required placeholder="P.O. Box 1234-00100 Nairobi" />
          </div>
          <div class="col-span-2">
            <Label>KRA PIN <span class="text-red-600">*</span></Label>
            <Input v-model="form.kra_pin" required placeholder="A001234567B" />
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <input type="checkbox" id="can_login" v-model="canLogin" />
          <Label for="can_login" class="font-normal">Allow this counsel to log in to the system</Label>
        </div>

        <div v-if="canLogin" class="grid grid-cols-2 gap-5 border-t pt-5">
          <div>
            <Label>Login Email <span class="text-red-600">*</span></Label>
            <Input v-model="form.email" type="email" required placeholder="advocate@firm.com" />
          </div>
          <div>
            <Label>Password <span class="text-red-600">*</span></Label>
            <Input v-model="form.password" type="password" required minlength="6" />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit">Create External Counsel</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>