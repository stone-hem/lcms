<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue';


import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'


const props = defineProps<{ open: boolean }>()
const emit = defineEmits(['close', 'success'])

const canLogin = ref(false)
const errors = ref({})

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
  const payload: any = {
    firm_name: form.value.firm_name.trim(),
    bank_name: form.value.bank_name.trim(),
    bank_branch: form.value.bank_branch?.trim() || null,
    bank_account_number: form.value.bank_account_number.trim(),
    postal_address: form.value.postal_address.trim(),
    kra_pin: form.value.kra_pin.trim(),
    can_login: canLogin.value,
  }

  // Only send email & password when login is enabled
  if (canLogin.value) {
    payload.email = form.value.email.trim()
    payload.password = form.value.password
  }

  try {
    await router.post('/external_council', payload, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        emit('success')
        emit('close')
        resetForm()
      },
      onError: (errors) => {
        errors.value = errors
      },
    })
  } catch (err) {
  }
}

const resetForm = () => {
  form.value = {
    firm_name: '',
    bank_name: '',
    bank_branch: '',
    bank_account_number: '',
    postal_address: '',
    kra_pin: '',
    email: '',
    password: '',
  }
  canLogin.value = false
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
      
      <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mx-8 mt-6">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 space-y-2">
              <div class="flex items-center gap-2 font-semibold">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>There were errors with your submission</span>
              </div>
              <ul class="ml-7 list-disc space-y-1 text-sm">
                <li v-for="(error, field) in $page.props.errors" :key="field">
                  {{ error }}
                </li>
              </ul>
            </div>
          </div>

      <form @submit.prevent="submit"  class="space-y-6">
          
        <div class="grid grid-cols-2 gap-5">
          <div class="col-span-2">
            <Label>Firm / Advocate Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.firm_name" required placeholder="e.g. Kimani & Associates" />
            <InputError :message="errors?.firm_name" />

          </div>

          <div>
            <Label>Bank Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.bank_name" required />
            <InputError :message="errors?.bank_name" />

          </div>

          <div>
            <Label>Bank Branch</Label>
            <Input v-model="form.bank_branch" placeholder="e.g. Westlands" />
            <InputError :message="errors?.bank_branch" />
          </div>

          <div>
            <Label>Bank Account Number <span class="text-red-600">*</span></Label>
            <Input v-model="form.bank_account_number" required />
            <InputError :message="errors?.bank_account_number" />
          </div>

          <div>
            <Label>Postal Address <span class="text-red-600">*</span></Label>
            <Input v-model="form.postal_address" required placeholder="P.O. Box 1234-00100 Nairobi" />
            <InputError :message="errors?.postal_address" />
          </div>

          <div class="col-span-2">
            <Label>KRA PIN <span class="text-red-600">*</span></Label>
            <Input v-model="form.kra_pin" required placeholder="A001234567B" />
            <InputError :message="errors?.kra_pin" />
          </div>
        </div>

        <!-- Login Toggle -->
        <div class="flex items-center space-x-2">
          <input type="checkbox" id="can_login" v-model="canLogin" class="w-4 h-4" />
          <Label for="can_login" class="font-normal cursor-pointer">
            Allow this counsel to log in to the system
          </Label>
        </div>

        <!-- Login Fields (only shown when canLogin = true) -->
        <div v-if="canLogin" class="grid grid-cols-2 gap-5 border-t pt-5">
          <div>
            <Label>Login Email <span class="text-red-600">*</span></Label>
            <Input v-model="form.email" type="email" required placeholder="advocate@firm.com" />
            <InputError :message="errors?.email" />
          </div>
          <div>
            <Label>Password <span class="text-red-600">*</span></Label>
            <Input v-model="form.password" type="password" required minlength="6" />
            <InputError :message="errors?.password" />
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