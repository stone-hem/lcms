<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Select from '@/components/form/Select.vue';
import DatePicker from '@/components/form/DatePicker.vue';

const props = defineProps<{
  open: boolean;
  presets: { roles: Array<{ id: number; name: string }> };
  isExternal?: boolean;
  isInternal?: boolean;
}>();
const emit = defineEmits(['close', 'success']);

const enableLogin = ref(false);
const changePassword = ref(false);

const form = useForm({
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  calling_code: '+254',
  phone: '',
  role_id: '',
  location: '',
  date_of_birth: '',
  enable_login: false,
  password: '',
  active: 1,
  is_internal: props.isInternal,
  is_external: props.isExternal,
});

const submit = () => {
  form.enable_login = enableLogin.value;
  form.location = JSON.stringify(form.location);
  form.post('/user', {
    onSuccess: () => {
      form.reset();
      enableLogin.value = false;
      emit('success');
    },
    preserveScroll: true,
  });
};


</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Add New User</DialogTitle>
        <DialogDescription>Create a new system user account.</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
            <Label>Last Name</Label>
            <Input v-model="form.last_name" placeholder="Doe" />
          </div>
          <div>
            <Label>Email <span class="text-red-600">*</span></Label>
            <Input v-model="form.email" type="email" required placeholder="john@example.com" />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <Label>Phone Calling Code <span class="text-red-600">*</span></Label>
            <Input v-model="form.calling_code" required placeholder="+254" />
          </div>
          <div>
            <Label>Phone Number <span class="text-red-600">*</span></Label>
            <Input v-model="form.phone" required placeholder="712345678" />
            <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</p>
          </div>
          <div>
            <Label>Role <span class="text-red-600">*</span></Label>
            <Select v-model="form.role_id" required>
              <option value="">Select role</option>
              <option v-for="role in presets.roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </Select>
          </div>
          <div>
            <Label>Date of Birth</Label>
            <DatePicker v-model="form.date_of_birth" />
          </div>
          <div>
            <Label>Location</Label>
            <Input v-model="form.location" placeholder="Nairobi, Kenya" />
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <input type="checkbox" id="enable_login" v-model="enableLogin" />
          <Label for="enable_login">Allow this user to login</Label>
        </div>

        <div v-if="enableLogin" class="space-y-4 border-t pt-4">
          <div>
            <Label>Password <span class="text-red-600">(min 6 chars)</span></Label>
            <Input v-model="form.password" type="password" placeholder="Enter password" />
            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create User' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>