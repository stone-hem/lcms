<script setup lang="ts">
import DatePicker from '@/components/form/DatePicker.vue';
import Select from '@/components/form/Select.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface User {
    id: number;
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    calling_code: string;
    phone: string;
    role_id: number;
    location: string;
    date_of_birth: string | null;
    can_login: boolean;
    is_active: boolean;
}

const props = defineProps<{
    open: boolean;
    user: User | null;
    presets: { roles: Array<{ id: number; name: string }> };
}>();
const emit = defineEmits(['close', 'success']);

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
    change_password: false,
    active: 1,
});

watch(
    () => props.user,
    (user) => {
        if (user) {
            form.reset();
            form.first_name = user.first_name;
            form.middle_name = user.middle_name || '';
            form.last_name = user.last_name || '';
            form.email = user.email;
            form.calling_code = user.calling_code;
            form.phone = user.phone;
            form.role_id = user.role_id.toString();
            form.location = JSON.parse(user.location) || '';
            form.date_of_birth = user.date_of_birth || '';
            form.enable_login = user.can_login;
            form.active = user.is_active ? 1 : 0;
        }
    },
    { immediate: true },
);

const submit = () => {
    form.change_password = changePassword.value;
    form.location = JSON.stringify(form.location);
    if (!changePassword.value) form.password = '';

    form.put(`/api/users/${props.user?.id}`, {
        onSuccess: () => {
            changePassword.value = false;
            emit('success');
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogContent class="max-h-[90vh] max-w-3xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Edit User</DialogTitle>
                <DialogDescription
                    >Update user information and permissions.</DialogDescription
                >
            </DialogHeader>

            <form v-if="user" @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <Label
                            >First Name
                            <span class="text-red-600">*</span></Label
                        >
                        <Input
                            v-model="form.first_name"
                            required
                            placeholder="John"
                        />
                        <p
                            v-if="form.errors.first_name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.first_name }}
                        </p>
                    </div>
                    <div>
                        <Label>Middle Name</Label>
                        <Input
                            v-model="form.middle_name"
                            placeholder="Kimani"
                        />
                    </div>
                    <div>
                        <Label>Last Name</Label>
                        <Input v-model="form.last_name" placeholder="Doe" />
                    </div>
                    <div>
                        <Label>Email <span class="text-red-600">*</span></Label>
                        <Input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="john@example.com"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>
                    <div>
                        <Label
                            >Phone Calling Code
                            <span class="text-red-600">*</span></Label
                        >
                        <Input
                            v-model="form.calling_code"
                            required
                            placeholder="+254"
                        />
                    </div>
                    <div>
                        <Label
                            >Phone Number
                            <span class="text-red-600">*</span></Label
                        >
                        <Input
                            v-model="form.phone"
                            required
                            placeholder="712345678"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>
                    <div>
                        <Label>Role <span class="text-red-600">*</span></Label>
                        <Select v-model="form.role_id" required>
                            <option value="">Select role</option>
                            <option
                                v-for="role in presets.roles"
                                :key="role.id"
                                :value="role.id"
                            >
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
                        <Input
                            v-model="form.location"
                            placeholder="Nairobi, Kenya"
                        />
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <input
                    type="checkbox"
                        id="enable_login_edit"
                        v-model="form.enable_login"
                    />
                    <Label for="enable_login_edit">Allow login access</Label>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox"
                        id="change_password"
                        v-model="changePassword"
                    />
                    <Label for="change_password">Change Password</Label>
                </div>

                <div v-if="changePassword" class="space-y-2">
                    <Label>New Password (min 6 chars)</Label>
                    <Input
                        v-model="form.password"
                        type="password"
                        placeholder="Leave blank to keep current"
                    />
                </div>

                <DialogFooter>
                    <DialogClose as-child
                        ><Button variant="outline">Cancel</Button></DialogClose
                    >
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Update User' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
