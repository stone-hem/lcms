<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import TextArea from '@/components/form/TextArea.vue'
import Select from '@/components/form/Select.vue'
import DatePicker from '@/components/form/DatePicker.vue'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
const props = defineProps<{
  open: boolean
  activity?: any
  caseActivityTypes: Array<{ id: number; name: string }>
  participants?: Array<{ id: number; name: string }>
}>()

const emit = defineEmits(['close', 'success'])

const selectedParticipants = ref<number[]>([])

const form = useForm({
  id: null,
  legal_case_id: props.activity?.legal_case_id,
  case_activity_id: '',
  status: 1,
  date: '',
  title: '',
  description: '',
  participants: [] as number[],
})

watch(
  () => props.activity,
  (act) => {
    if (act && props.open) {
      form.id = act.id
      form.legal_case_id = act.legal_case_id
      form.case_activity_id = act.case_activity_id || act.activity?.id
      form.status = act.status
      form.date = act.date
      form.title = act.title
      form.description = act.description || ''
      selectedParticipants.value = act.participants?.map(p => p.user_id || p.id) || []
    }
  },
  { immediate: true }
)

const submit = () => {
  form.participants = selectedParticipants.value
  form.put(`/legal_cases/case-activities/${form.id}`, {
    onSuccess: () => {
      emit('success')
      emit('close')
    },
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>Edit Activity</DialogTitle>
        <DialogDescription>Update "{{ activity?.title }}"</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>Title <span class="text-red-600">*</span></Label>
            <Input v-model="form.title" required placeholder="e.g. Initial Hearing" />
          </div>

          <div>
            <Label>Activity Type <span class="text-red-600">*</span></Label>
            <Select v-model="form.case_activity_id" required>
              <option value="">Select activity type</option>
              <option v-for="type in caseActivityTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </Select>
          </div>

          <div>
            <Label>Date <span class="text-red-600">*</span></Label>
            <DatePicker v-model="form.date" required />
          </div>

          <div>
            <Label>Status</Label>
            <Select v-model="form.status">
              <option value="1">Pending</option>
              <option value="2">In Progress</option>
              <option value="3">Completed</option>
              <option value="4">Cancelled</option>
            </Select>
          </div>
        </div>

        <div>
          <Label>Description (Optional)</Label>
          <TextArea
            v-model="form.description"
            placeholder="Add details about this activity..."
          />
        </div>

        <div>
          <Label>Participants (Optional)</Label>
          <div class="flex flex-wrap gap-2 mt-2">
            <label v-for="p in participants" :key="p.id" class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                :value="p.id"
                v-model="selectedParticipants"
                class="rounded border-gray-300 text-primary focus:ring-primary"
              />
              <span class="text-sm">{{ p.name }}</span>
            </label>
          </div>
        </div>

        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="outline">Cancel</Button>
          </DialogClose>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Add Activity' }}
          </Button>
        </DialogFooter>
      </form>
      <DialogFooter>
        <DialogClose as-child><Button variant="outline">Cancel</Button></DialogClose>
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Updating...' : 'Update Activity' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>