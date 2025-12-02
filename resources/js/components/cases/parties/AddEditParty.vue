<!-- components/cases/parties/GenericPartyModal.vue -->
<script setup lang="ts">
import { watch, ref, computed } from "vue"
import { useForm } from "@inertiajs/vue3"
import { Button } from "@/components/ui/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import Select from "@/components/form/Select.vue"
import DisplayErrors from '@/components/ui/DisplayErrors.vue'


const props = defineProps<{
  open: boolean
  party?: any
  partyTypes: Array<{ id: number; name: string }>
  type?: "individual" | "firm"
  legalCaseId: number
}>()

const emit = defineEmits<{
  close: []
  success: []
}>()

const partyKind = computed(() =>
  props.type || (props.party?.party?.party_kind === "firm" ? "firm" : "individual")
)

const isEdit = computed(() => !!props.party)

const form = useForm({
  party_type_id: "",
  // Individual
  first_name: "",
  middle_name: "",
  last_name: "",
  gender: "",
  birth_date: "",
  // Firm
  name: "",
  // Shared
  email: "",
  phone: "",
  physical_address: "",
  postal_address: "",
  // more:
  party_kind: "",
  legal_case_id: null as number | null,
})

// Populate form when editing
watch(
  () => props.party,
  (p) => {
    if (!p || !props.open) {
      form.reset()
      return
    }

    form.party_type_id = p.party_type_id ?? ""

    const party = p.party

    if (partyKind.value === "individual") {
      form.first_name = party.first_name ?? ""
      form.middle_name = party.middle_name ?? ""
      form.last_name = party.last_name ?? ""
      form.gender = party.gender ?? ""
      form.birth_date = party.birth_date ?? ""
    } else {
      form.name = party.name ?? ""
    }

    form.email = party.email ?? ""
    form.phone = party.phone ?? ""
    form.physical_address = party.physical_address ?? ""
    form.postal_address = party.postal_address ?? ""
  },
  { immediate: true }
)

const submit = () => {
  // Update the form fields before submitting
  form.party_kind = partyKind.value
  form.legal_case_id = props.legalCaseId

  if (isEdit.value) {
    form.put(`/parties/${props.party.party.id}`, {
      onSuccess: () => {
          emit("success")
          emit("close")
      },
      preserveScroll: true,
    })
  } else {
    form.post("/parties", {
      onSuccess: () => {
          emit("success")
          emit("close")
      },
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('close')">
    <DialogContent class="max-w-3xl max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>
          {{ isEdit ? "Edit" : "Add" }}
          {{ partyKind === "individual" ? "Individual" : "Firm" }}
        </DialogTitle>
        <DialogDescription>
          {{ isEdit ? "Update the details" : "Add a new party" }} for this case.
        </DialogDescription>
      </DialogHeader>
      
      <DisplayErrors :errors="$page.props.errors" />


      <form @submit.prevent="submit" class="space-y-6">
        <!-- Individual fields -->
        <template v-if="partyKind === 'individual'">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label>First Name *</Label>
              <Input v-model="form.first_name" required />
            </div>
            <div>
              <Label>Middle Name</Label>
              <Input v-model="form.middle_name" />
            </div>
            <div>
              <Label>Last Name *</Label>
              <Input v-model="form.last_name" required />
            </div>
            <div>
              <Label>Date of Birth *</Label>
              <Input v-model="form.birth_date" type="date" required />
            </div>
            <div>
              <Label>Gender *</Label>
              <Select v-model="form.gender" required>
                <option value="" disabled>Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </Select>
            </div>
          </div>
        </template>

        <!-- Firm field -->
        <template v-else>
          <div>
            <Label>Firm Name *</Label>
            <Input v-model="form.name" required placeholder="ABC Advocates LLP" />
          </div>
        </template>

        <!-- Shared fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>Email *</Label>
            <Input v-model="form.email" type="email" required />
          </div>
          <div>
            <Label>Phone *</Label>
            <Input v-model="form.phone" required />
          </div>
          <div class="md:col-span-2">
            <Label>Party Type *</Label>
            <Select v-model="form.party_type_id" required>
              <option value="" disabled>Select type</option>
              <option v-for="t in partyTypes" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </Select>
          </div>
        </div>

        <!-- Address (optional) -->
        <div class="space-y-3 border-t pt-4">
          <h4 class="font-medium">Address (Optional)</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <Label>Physical Address</Label>
              <Input v-model="form.physical_address" />
            </div>
            <div class="md:col-span-2">
              <Label>Postal Address</Label>
              <Input v-model="form.postal_address" />
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="emit('close')">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? "Saving…" : isEdit ? "Save Changes" : "Add Party" }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>