<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue'
import AddInternalCounselModal from '@/components/users/AddInternalCounselModal.vue'
import EditUserModal from '@/components/users/EditUserModal.vue'
import DeleteUserModal from '@/components/users/DeleteUserModal.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Plus } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

interface User {
  id: number
  name: string
  email: string
  phone: string
  calling_code: string
  role: { name: string }
  can_login: boolean
  created_at: string
  deleted_at?: string | null
}

const props = withDefaults(defineProps<{
  users: any
  total_count: number
  filters: { search: string }
  presets: { roles: Array<{ id: number; name: string }> }
}>(), {})

const searchQuery = ref(props.filters.search)

// watch(searchQuery, debounce((value) => {
//   router.get(route('users.index'), {
//     search: value || null,
//     ic: 1, // Force internal counsel only
//     page: 1,
//   }, { preserveState: true, replace: true })
// }, 400))

const selectedUser = ref<User | null>(null)
const showAdd = ref(false)
const showEdit = ref(false)
const showDelete = ref(false)

const openAdd = () => (showAdd.value = true)
const openEdit = (user: User) => {
  selectedUser.value = user
  showEdit.value = true
}
const openDelete = (user: User) => {
  selectedUser.value = user
  showDelete.value = true
}
const closeAll = () => {
  showAdd.value = showEdit.value = showDelete.value = false
  selectedUser.value = null
}
const refresh = () => router.visit(route('users.index'), { preserveState: true })

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString() : 'N/A'
</script>

<template>
  <AppLayout>
    <Head title="Internal Counsel" />
    <div class="flex flex-col space-y-8 p-8">
      <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <HeadingSmall
          title="Internal Counsel"
          description="Manage internal legal counsel users"
        />
        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
          <Input v-model="searchQuery" placeholder="Search name, email, phone..." class="w-full sm:w-80" />
          <Button @click="openAdd">
            <Plus class="mr-2 h-4 w-4" />
            Add Internal Counsel
          </Button>
        </div>
      </div>

      <Card class="rounded-xl border shadow-sm">
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Phone</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Login</TableHead>
                <TableHead>Created</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="user in users.data" :key="user.id">
                <TableCell class="font-medium">{{ user.name }}</TableCell>
                <TableCell>{{ user.email }}</TableCell>
                <TableCell>{{ user.calling_code }}{{ user.phone }}</TableCell>
                <TableCell>
                  <Badge :variant="user.deleted_at ? 'destructive' : 'default'">
                    {{ user.deleted_at ? 'Inactive' : 'Active' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Badge :variant="user.can_login ? 'default' : 'secondary'">
                    {{ user.can_login ? 'Yes' : 'No' }}
                  </Badge>
                </TableCell>
                <TableCell>{{ formatDate(user.created_at) }}</TableCell>
                <TableCell class="text-right">
                  <Button variant="outline" size="sm" @click="openEdit(user)">Edit</Button>
                  <Button variant="destructive" size="sm" class="ml-2" @click="openDelete(user)">Delete</Button>
                </TableCell>
              </TableRow>
              <TableRow v-if="!users.data.length">
                <TableCell colspan="7" class="text-center py-12">
                  <div class="space-y-3">
                    <p class="text-lg font-medium">No internal counsel found</p>
                    <Button @click="openAdd">Add Your First Counsel</Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div class="flex items-center justify-between mt-6">
        <div class="text-sm text-gray-600">
          Showing {{ users.from }} to {{ users.to }} of {{ total_count }} counsel
          <span v-if="filters.search" class="text-blue-600 font-medium"> (filtered)</span>
        </div>
        <nav v-if="users.links?.length > 3" class="flex gap-1">
          <Button
            v-for="(link, i) in users.links"
            :key="i"
            :variant="link.active ? 'default' : 'outline'"
            size="sm"
            :disabled="!link.url"
            @click="link.url && router.get(link.url)"
            v-html="link.label"
          />
        </nav>
      </div>
    </div>

    <!-- Dedicated Add Modal for Internal Counsel -->
    <AddInternalCounselModal
      :open="showAdd"
      :presets="presets"
      @close="showAdd = false"
      @success="closeAll(); refresh()"
    />
    <EditUserModal
      :open="showEdit"
      :user="selectedUser"
      :presets="presets"
      @close="closeAll"
      @success="closeAll(); refresh()"
    />
    <DeleteUserModal
      :open="showDelete"
      :user="selectedUser"
      @close="closeAll"
      @success="closeAll(); refresh()"
    />
  </AppLayout>
</template>