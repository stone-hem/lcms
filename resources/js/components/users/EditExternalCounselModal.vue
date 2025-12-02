<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{ open: boolean; firm: any }>()
const emit = defineEmits(['close', 'success'])

const firm = ref({ ...props.firm })
const canLogin = ref(!!props.firm?.user_id)
const changePassword = ref(false)
const newPassword = ref('')

watch(() => props.firm, (newVal) => {
  if (newVal) {
    firm.value = { ...newVal }
    canLogin.value = !!newVal.user_id
  }
}, { immediate: true })

const submit = () => {
  const payload: any = {
    firm_name: firm.value.firm_name,
    bank_name: firm.value.bank_name,
    bank_branch: firm.value.bank_branch || null,
    bank_account_number: firm.value.bank_account_number,
    postal_address: firm.value.postal_address,
    kra_pin: firm.value.kra_pin,
    can_login: canLogin.value,
  }

  if (canLogin.value) {
    payload.email = firm.value.email || firm.value.user?.email
    if (changePassword.value && newPassword.value) {
      payload.password = newPassword.value
    }
  }

  router.put(`/external_council/${firm.value.id}`, payload, {
    onSuccess: () => {
      emit('success')
      emit('close')
    },
    onError: (errors) => {
      console.error(errors)
    }
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle>Edit External Counsel</DialogTitle>
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

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <Label>Firm Name <span class="text-red-600">*</span></Label>
            <Input v-model="firm.firm_name" required />
          </div>
          <div><Label>Bank Name</Label><Input v-model="firm.bank_name" required /></div>
          <div><Label>Bank Branch</Label><Input v-model="firm.bank_branch" /></div>
          <div><Label>Account Number</Label><Input v-model="firm.bank_account_number" required /></div>
          <div><Label>Postal Address</Label><Input v-model="firm.postal_address" required /></div>
          <div class="col-span-2"><Label>KRA PIN</Label><Input v-model="firm.kra_pin" required /></div>
        </div>

        <div class="border-t pt-4 space-y-4">
          <div class="flex items-center gap-2">
            <input type="checkbox" id="can_login" v-model="canLogin" />
            <Label for="can_login">Allow this firm to log in</Label>
          </div>

          <div v-if="canLogin">
            <div class="text-sm text-muted-foreground">
              Current email: <strong>{{ firm.user?.email || firm.email || 'Not set' }}</strong>
            </div>

            <div class="flex items-center gap-2">
              <input type="checkbox" id="change_pwd" v-model="changePassword" />
              <Label for="change_pwd">Change password</Label>
            </div>

            <div v-if="changePassword">
              <Label>New Password</Label>
              <Input v-model="newPassword" type="password" placeholder="Enter new password" />
            </div>
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit">Update</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>