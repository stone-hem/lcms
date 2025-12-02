<!-- components/cases/parties/DeletePartyModal.vue -->
<script setup lang="ts">
import { useForm } from "@inertiajs/vue3"
import { Button } from "@/components/ui/button"
import {
  Dialog, DialogContent, DialogDescription,
  DialogFooter, DialogHeader, DialogTitle
} from "@/components/ui/dialog"
import { Trash2 } from "lucide-vue-next"

const props = defineProps<{
  open: boolean
  party?: any
}>()

const emit = defineEmits(["close", "deleted"])

const form = useForm({})

const destroy = () => {
  if (!props.party) return

  form.delete(`/parties/${props.party.party.id}`, {
    onSuccess: () => emit("deleted"),
    onFinish: () => emit("close"),
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent>
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2 text-destructive">
          <Trash2 class="h-5 w-5" />
          Delete Party
        </DialogTitle>
        <DialogDescription>
          Are you sure you want to delete this party? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>

      <div class="py-6 text-center">
        <div class="mx-auto w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mb-4">
          <Trash2 class="h-8 w-8 text-destructive" />
        </div>
        <p class="font-medium text-lg">
          {{ party?.party?.first_name 
             ? `${party.party.first_name} ${party.party.last_name || ''}` 
             : party?.party?.name }}
        </p>
        <p class="text-sm text-muted-foreground mt-1">
          {{ party?.party?.email }}
        </p>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="emit('close')">Cancel</Button>
        <Button variant="destructive" @click="destroy" :disabled="form.processing">
          {{ form.processing ? "Deleting..." : "Yes, Delete" }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>