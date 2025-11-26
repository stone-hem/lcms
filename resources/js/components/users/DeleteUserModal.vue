<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  open: boolean;
  user: { id: number; name: string } | null;
}>();
const emit = defineEmits(['close', 'success']);

const form = useForm({});

const confirmDelete = () => {
  form.delete(`/api/users/${props.user?.id}`, {
    onSuccess: () => emit('success'),
  });
};
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Delete User</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete <strong>{{ user?.name }}</strong>? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" :disabled="form.processing" @click="confirmDelete">
          {{ form.processing ? 'Deleting...' : 'Delete User' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>