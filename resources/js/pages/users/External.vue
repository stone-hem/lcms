<!-- resources/js/Pages/counsel/ExternalCounselIndex.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import AddExternalCounselModal from '@/components/users/AddExternalCounselModal.vue'
import EditExternalCounselModal from '@/components/users/EditExternalCounselModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Plus, Trash2, ToggleLeft, ToggleRight, Edit3 } from 'lucide-vue-next'

const props = defineProps<{
  firms: {
    data: any[]
    current_page: number
    total: number
    per_page: number
    from: number | null
    to: number | null
  }
  filters: { s?: string }
}>()

const searchQuery = ref(props.filters.s || '')
const showAdd = ref(false)
const showEdit = ref(false)
const selectedFirm = ref<any>(null)

// Watch for URL changes (e.g. after search)
watch(() => props.filters.s, (val) => {
  searchQuery.value = val || ''
})

// Search with debounce
let timeout: any
const search = () => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get(
      '/external_council',
      { s: searchQuery.value || null },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      }
    )
  }, 400)
}

const openEdit = (firm: any) => {
  selectedFirm.value = { ...firm }
  showEdit.value = true
}

// Optional: Success handler from modals
const onSuccess = () => {
  router.reload({ only: ['firms'] })
}
</script>

<template>
  <AppLayout>
    <Head title="External Counsel" />

    <div class="flex flex-col space-y-8 p-8">
      <!-- Header -->
      <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <HeadingSmall
          title="External Counsel"
          description="Manage external law firms and advocates"
        />

        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
          <Input
            v-model="searchQuery"
            @input="search"
            @keyup.enter="search"
            placeholder="Search by firm name, email or KRA PIN..."
            class="w-full sm:w-80"
          />
          <Button @click="showAdd = true">
            <Plus class="mr-2 h-4 w-4" />
            Add External Counsel
          </Button>
        </div>
      </div>

      <!-- Table -->
      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Firm Name</TableHead>
                <TableHead>Login Email</TableHead>
                <TableHead>KRA PIN</TableHead>
                <TableHead>Bank Details</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Created</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="firm in firms.data" :key="firm.id">
                <TableCell class="font-medium">
                  {{ firm.firm_name }}
                  <Badge v-if="firm.deleted_at" variant="destructive" class="ml-2 text-xs">
                    Soft Deleted
                  </Badge>
                </TableCell>

                <TableCell>
                  <template v-if="firm.user">
                    <Badge variant="outline" class="font-mono text-xs">
                      {{ firm.user.email }}
                    </Badge>
                  </template>
                  <Badge v-else variant="secondary">No Login</Badge>
                </TableCell>

                <TableCell class="font-mono">{{ firm.kra_pin }}</TableCell>

                <TableCell class="text-sm">
                  {{ firm.bank_name }} ({{ firm.bank_account_number }})
                </TableCell>

                <TableCell>
                  <Badge :variant="firm.deleted_at ? 'destructive' : firm.can_login ? 'default' : 'secondary'">
                    {{ firm.deleted_at ? 'Deleted' : firm.can_login ? 'Active' : 'Inactive' }}
                  </Badge>
                </TableCell>

                <TableCell>
                  {{ new Date(firm.created_at).toLocaleDateString('en-KE') }}
                </TableCell>

                <TableCell class="text-right space-x-1">
                  <Button size="sm" variant="ghost" @click="openEdit(firm)" title="Edit">
                    <Edit3 class="h-4 w-4" />
                  </Button>

                  <Button
                    v-if="firm.deleted_at"
                    size="sm"
                    variant="default"
                    @click="router.post(`/external_council/${firm.id}/restore`)"
                    title="Restore"
                  >
                    <ToggleRight class="h-4 w-4" />
                  </Button>

                  <Button
                    v-else
                    size="sm"
                    variant="secondary"
                    @click="router.delete(`/external_council/${firm.id}`, { 
                      onBefore: () => confirm('Deactivate this firm? They will lose access.')
                    })"
                    title="Deactivate"
                  >
                    <ToggleLeft class="h-4 w-4" />
                  </Button>

                  <Button
                    size="sm"
                    variant="destructive"
                    @click="router.delete(`/external_council/${firm.id}/force`, {
                      onBefore: () => confirm('Permanently delete this firm and all data? This cannot be undone.')
                    })"
                    title="Delete Permanently"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <!-- Empty State -->
          <div v-if="firms.data.length === 0" class="p-12 text-center text-muted-foreground">
            <p>No external counsel found.</p>
            <Button @click="showAdd = true" class="mt-4">
              <Plus class="mr-2 h-4 w-4" />
              Add Your First Firm
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination Info -->
      <div class="text-sm text-muted-foreground">
        Showing {{ firms.from || 0 }} to {{ firms.to || 0 }} of {{ firms.total }} results
      </div>
    </div>

    <!-- Modals -->
    <AddExternalCounselModal
      :open="showAdd"
      @close="showAdd = false"
      @success="onSuccess"
    />

    <EditExternalCounselModal
      v-if="selectedFirm"
      :open="showEdit"
      :firm="selectedFirm"
      @close="showEdit = false"
      @success="onSuccess"
    />
  </AppLayout>
</template>