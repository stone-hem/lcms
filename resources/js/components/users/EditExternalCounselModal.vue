<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{ open: boolean; firm: any }>()
const emit = defineEmits(['close', 'success'])

const firm = ref({ ...props.firm })
const canLogin = ref(!!firm.value.user)
const changePassword = ref(false)
const newPassword = ref('')

watch(() => props.firm, (newFirm) => {
  if (newFirm) {
    firm.value = { ...newFirm }
    canLogin.value = !!newFirm.user
  }
})

const submit = async () => {
  try {
    const payload: any = {
      id: firm.value.id,
      firm_name: firm.value.firm_name,
      bank_name: firm.value.bank_name,
      bank_branch: firm.value.bank_branch,
      bank_account_number: firm.value.bank_account_number,
      postal_address: firm.value.postal_address,
      kra_pin: firm.value.kra_pin,
      can_login: canLogin.value,
      user_id: firm.value.user?.id || null,
      email: firm.value.user?.email || '',
    }

    if (canLogin.value && changePassword.value) {
      payload.change_password = true
      payload.new_password = newPassword.value
    }

    await axios.put(`/api/external-firms/${firm.value.id}`, payload)
    emit('success')
    emit('close')
  } catch (err: any) {
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit External Counsel</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-5">
          <div class="col-span-2">
            <Label>Firm / Advocate Name</Label>
            <Input v-model="firm.firm_name" required />
          </div>
          <div><Label>Bank Name</Label><Input v-model="firm.bank_name" required /></div>
          <div><Label>-bank Branch</Label><Input v-model="firm.bank_branch" required /></div>
          <div><Label>Account Number</Label><Input v-model="firm.bank_account_number" required /></div>
          <div><Label>Postal Address</Label><Input v-model="firm.postal_address" required /></div>
          <div class="col-span-2"><Label>KRA PIN</Label><Input v-model="firm.kra_pin" required /></div>
        </div>

        <div class="space-y-4 border-t pt-5">
          <div class="flex items-center space-x-2">
            <input type="checkbox" id="can_login_edit" v-model="canLogin" />
            <Label for="can_login_edit">Allow login access</Label>
          </div>

          <template v-if="canLogin">
            <div>
              <Label>Current Login Email</Label>
              <Input :value="firm.user?.email || 'Not set'" disabled />
            </div>
            <div class="flex items-center space-x-2">
              <input type="checkbox" id="change_pwd" v-model="changePassword" />
              <Label for="change_pwd">Change password</Label>
            </div>
            <div v-if="changePassword">
              <Label>New Password</Label>
              <Input v-model="newPassword" type="password" required minlength="6" placeholder="Leave blank to keep current" />
            </div>
          </template>
        </div>

        <DialogFooter>
          <DialogClose as-child><Button type="button" variant="outline">Cancel</Button></DialogClose>
          <Button type="submit">Update Counsel</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>