<!-- resources/js/components/legal-cases/modals/DeleteCaseModal.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
  DialogFooter, DialogClose
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  open: boolean
  case?: any
}>()

const emit = defineEmits(['close', 'success'])

const form = useForm({})

const submit = () => {
  if (!props.case?.id) return

  form.delete(route('case.delete', props.case.id), {
    onSuccess: () => {
      emit('success')
    },
    preserveScroll: true,
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Delete Legal Case</DialogTitle>
        <DialogDescription>
          This action <strong>cannot be undone</strong>. This will permanently delete the case.
        </DialogDescription>
      </DialogHeader>

      <div v-if="props.case" class="my-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex items-center gap-2 text-red-800 dark:text-red-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="font-semibold">Case to be deleted:</span>
        </div>
        <p class="mt-2 text-sm text-red-700 dark:text-red-300 font-medium">
          {{ props.case.title }}
        </p>
        <p class="text-xs text-red-600 dark:text-red-400">
          {{ props.case.case_number || 'No case number' }}
        </p>
      </div>

      <DialogFooter>
        <DialogClose as-child>
          <Button type="button" variant="outline">Cancel</Button>
        </DialogClose>
        <Button
          type="button"
          variant="destructive"
          @click="submit"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Deleting...' : 'Yes, Delete Case' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>