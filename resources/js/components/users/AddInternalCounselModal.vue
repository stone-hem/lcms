<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{ open: boolean; presets: any }>()
const emit = defineEmits(['close', 'success'])

const form = useForm({
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  phone: '',
  calling_code: '+254',
  password: '',
  password_confirmation: '',
  can_login: true,
  role_id: 2, // Fixed: Internal Counsel
})

const submit = () => {
  form.post(route('users.store'), {
    onSuccess: () => {
      form.reset()
      emit('success')
      emit('close')
    },
    preserveScroll: true,
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Add Internal Counsel</DialogTitle>
        <DialogDescription>
          Create a new internal legal counsel user account.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-5">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label>First Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.first_name" required placeholder="John" />
            <p v-if="form.errors.first_name" class="text-sm text-red-600 mt-1">{{ form.errors.first_name }}</p>
          </div>
          <div>
            <Label>Middle Name</Label>
            <Input v-model="form.middle_name" placeholder="Kimani" />
          </div>
          <div>
            <Label>Last Name <span class="text-red-600">*</span></Label>
            <Input v-model="form.last_name" required placeholder="Doe" />
            <p v-if="form.errors.last_name" class="text-sm text-red-600 mt-1">{{ form.errors.last_name }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Email <span class="text-red-600">*</span></Label>
            <Input v-model="form.email" type="email" required placeholder="john@example.com" />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <Label>Phone Number <span class="text-red-600">*</span></Label>
            <div class="flex">
              <Input v-model="form.calling_code" class="w-24" placeholder="+254" />
              <Input v-model="form.phone" required placeholder="712345678" class="flex-1 ml-2" />
            </div>
            <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <input type="checkbox" id="can_login" v-model="form.can_login" />
          <Label for="can_login" class="font-normal">Allow this user to log in</Label>
        </div>

        <div v-if="form.can_login" class="grid grid-cols-2 gap-4">
          <div>
            <Label>Password <span class="text-red-600">*</span></Label>
            <Input v-model="form.password" type="password" required minlength="6" />
            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
          </div>
          <div>
            <Label>Confirm Password <span class="text-red-600">*</span></Label>
            <Input v-model="form.password_confirmation" type="password" required />
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create Counsel' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>