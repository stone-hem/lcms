<!-- resources/js/Pages/counsel/ExternalCounselIndex.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import AddExternalCounselModal from '@/components/users/AddExternalCounselModal.vue'
import EditExternalCounselModal from '@/components/users/EditExternalCounselModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Plus, Trash2, ToggleLeft, ToggleRight } from 'lucide-vue-next'

defineProps<{
  items: any[]
  total_count: number
  filters: { s: string }
}>()

const searchQuery = ref('')

const search = () => {
  router.get(route('counsel.external'), { s: searchQuery.value || null }, {
    preserveState: true,
    replace: true,
  })
}

const showAdd = ref(false)
const showEdit = ref(false)
const selectedFirm = ref(null)

const openEdit = (firm: any) => {
  selectedFirm.value = firm
  showEdit.value = true
}
</script>

<template>
  <AppLayout>
    <Head title="External Counsel" />

    <div class="flex flex-col space-y-8 p-8">
      <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <HeadingSmall
          title="External Counsel"
          description="Manage external law firms and advocates"
        />
        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
          <Input
            v-model="searchQuery"
            @keyup.enter="search"
            placeholder="Search firm name..."
            class="w-full sm:w-80"
          />
          <Button @click="showAdd = true">
            <Plus class="mr-2 h-4 w-4" />
            Add External Counsel
          </Button>
        </div>
      </div>

      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Firm Name</TableHead>
                <TableHead>Email (Login)</TableHead>
                <TableHead>KRA PIN</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Created</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="firm in items" :key="firm.id">
                <TableCell class="font-medium">{{ firm.firm_name }}</TableCell>
                <TableCell>
                  <template v-if="firm.user">
                    <Badge variant="outline">{{ firm.user.email }}</Badge>
                  </template>
                  <Badge v-else variant="secondary">No Login</Badge>
                </TableCell>
                <TableCell>{{ firm.kra_pin }}</TableCell>
                <TableCell>
                  <Badge :variant="firm.deleted_at ? 'destructive' : 'default'">
                    {{ firm.deleted_at ? 'Inactive' : 'Active' }}
                  </Badge>
                </TableCell>
                <TableCell>{{ new Date(firm.created_at).toLocaleDateString() }}</TableCell>
                <TableCell class="text-right space-x-2">
                  <Button size="sm" variant="outline" @click="openEdit(firm)">Edit</Button>

                  <Button
                    v-if="firm.deleted_at"
                    size="sm"
                    variant="default"
                    @click="router.post(route('counsel.external.activate', firm.id))"
                  >
                    <ToggleRight class="h-4 w-4 mr-1" /> Activate
                  </Button>
                  <Button
                    v-else
                    size="sm"
                    variant="secondary"
                    @click="router.post(route('counsel.external.deactivate', firm.id))"
                  >
                    <ToggleLeft class="h-4 w-4 mr-1" /> Deactivate
                  </Button>

                  <Button
                    size="sm"
                    variant="destructive"
                    @click="router.post(route('counsel.external.delete', firm.id), {}, { 
                      onBefore: () => confirm('Permanently delete this counsel?') 
                    })"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <div class="text-sm text-muted-foreground">
        Showing {{ items.length }} of {{ total_count }} external counsel
      </div>
    </div>

    <AddExternalCounselModal :open="showAdd" @close="showAdd = false" />
    <EditExternalCounselModal
      :open="showEdit"
      :firm="selectedFirm"
      @close="showEdit = false"
    />
  </AppLayout>
</template>