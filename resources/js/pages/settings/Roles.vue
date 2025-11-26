<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import AddRoleModal from '@/components/roles/AddRoleModal.vue'
import EditRoleModal from '@/components/roles/EditRoleModal.vue'
import AssignPermissionsModal from '@/components/roles/AssignPermissionsModal.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Plus, Edit, Trash2, Shield, RotateCcw } from 'lucide-vue-next'


defineProps<{
  roles: any[]
  all_permissions: any[]
}>()

const showAdd = ref(false)
const showEdit = ref(false)
const showPermissions = ref(false)
const selectedRole = ref(null)

const openEdit = (role) => {
  selectedRole.value = role
  showEdit.value = true
}

const openPermissions = (role) => {
  selectedRole.value = role
  showPermissions.value = true
}

const deleteRole = (role) => {
  if (!confirm('Delete this role?')) return
  router.delete(route('roles.destroy', role.id))
}

const restoreRole = (role) => {
  router.post(route('roles.restore', role.id))
}
</script>

<template>
  <AppLayout>
    <Head title="Roles & Permissions" />

    <div class="p-8 space-y-8">
      <div class="flex justify-between items-start">
        <HeadingSmall
          title="Roles Management"
          description="Manage system roles and their permissions"
        />
        <Button @click="showAdd = true">
          <Plus class="mr-2 h-4 w-4" />
          Add Role
        </Button>
      </div>

      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Permissions</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="role in roles" :key="role.id">
                <TableCell class="font-medium">{{ role.name }}</TableCell>
                <TableCell>{{ role.description || '—' }}</TableCell>
                <TableCell>
                  <Badge :variant="role.deleted_at ? 'destructive' : 'default'">
                    {{ role.deleted_at ? 'Inactive' : 'Active' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Badge variant="outline">
                    {{ role.permissions.length }} permission(s)
                  </Badge>
                </TableCell>
                <TableCell class="text-right space-x-2">
                  <Button size="sm" variant="ghost" @click="openPermissions(role)" title="Assign Permissions">
                    <Shield class="h-4 w-4" />
                  </Button>
                  <Button size="sm" variant="ghost" @click="openEdit(role)">
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button
                    v-if="role.deleted_at"
                    size="sm"
                    variant="ghost"
                    @click="restoreRole(role)"
                    title="Restore"
                  >
                    <RotateCcw class="h-4 w-4" />
                  </Button>
                  <Button size="sm" variant="destructive" @click="deleteRole(role)">
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <AddRoleModal :open="showAdd" @close="showAdd = false" />
    <EditRoleModal :open="showEdit" :role="selectedRole" @close="showEdit = false" />
    <AssignPermissionsModal
      :open="showPermissions"
      :role="selectedRole"
      :all-permissions="all_permissions"
      @close="showPermissions = false"
    />
  </AppLayout>
</template>