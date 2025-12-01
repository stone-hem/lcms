<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AddUserModal from '@/components/users/AddUserModal.vue';
import EditUserModal from '@/components/users/EditUserModal.vue';
import DeleteUserModal from '@/components/users/DeleteUserModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  calling_code: string;
  role: { name: string };
  is_active: boolean;
  can_login: boolean;
  created_at: string;
  deleted_at?: string | null;
}

const props = withDefaults(defineProps<{
  users: any; // Laravel paginated resource
  total_count: number;
  filters: {
    search: string;
    ic: boolean;
    sort_by: string;
    sort_desc: boolean;
  };
  presets: {
    roles: Array<{ id: number; name: string }>;
  };
}>(), {});

const page = usePage();

// Reactive search input
const searchQuery = ref(props.filters.search);

// Debounced search
// watch(searchQuery, debounce((value) => {
//   router.get(route('users.index'), {
//     search: value || null,
//     ic: props.filters.ic ? 1 : 0,
//     page: 1,
//   }, {
//     preserveState: true,
//     replace: true,
//   });
// }, 400));

// Modals
const selectedUser = ref<User | null>(null);
const showAdd = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

const openAdd = () => (showAdd.value = true);
const openEdit = (user: User) => {
  selectedUser.value = user;
  showEdit.value = true;
};
const openDelete = (user: User) => {
  selectedUser.value = user;
  showDelete.value = true;
};
const closeAll = () => {
  showAdd.value = showEdit.value = showDelete.value = false;
  selectedUser.value = null;
};

const refresh = () => router.visit(route('users.index'), { preserveState: true });

const formatDate = (date: string) =>
  date ? new Date(date).toLocaleDateString() : 'N/A';
</script>

<template>
  <AppLayout>
    <Head title="Users Management" />

    <div class="flex flex-col space-y-8 p-8">
      <!-- Header -->
      <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <HeadingSmall
          :title="filters.ic ? 'Internal Counsel' : 'Users'"
          :description="filters.ic ? 'Managing internal legal counsel only' : 'Manage all internal system users'"
        />

        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
          <Input
            v-model="searchQuery"
            placeholder="Search by name, email, phone, role..."
            class="w-full sm:w-80"
          />
          <Button @click="openAdd">
            <Plus class="mr-2 h-4 w-4" />
            Add User
          </Button>
        </div>
      </div>

      <!-- Table -->
      <Card class="rounded-xl border shadow-sm">
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Phone</TableHead>
                <TableHead>Role</TableHead>
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
                  <Badge variant="outline">{{ user.role.name }}</Badge>
                </TableCell>
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
                <TableCell>
                  <div class="flex justify-end gap-2">
                    <Button variant="outline" size="sm" @click="openEdit(user)">
                      Edit
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      @click="openDelete(user)"
                    >
                      Delete
                    </Button>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-if="users.data.length === 0">
                <TableCell colspan="8" class="py-12 text-center">
                  <div class="flex flex-col items-center space-y-3">
                    <p class="text-lg font-medium">No users found</p>
                    <Button @click="openAdd">Add Your First User</Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <!-- Pagination & Info -->
      <!-- Pagination & Info -->
      <div class="flex items-center justify-between mt-6">
        <!-- Left: Showing X to Y of Z -->
        <div class="text-sm text-gray-600">
          <span v-if="users.data.length">
            Showing {{ users.from }} to {{ users.to }} of {{ total_count }} users
          </span>
          <span v-else>
            No users found
          </span>
          <span v-if="filters.search" class="text-blue-600 font-medium">
            (filtered by "{{ filters.search }}")
          </span>
        </div>
      
        <!-- Right: Pagination Links -->
        <nav v-if="users.links && users.links.length > 3" class="flex items-center gap-1">
          <!-- Previous Button -->
          <Button
            :variant="users.prev_page_url ? 'outline' : 'ghost'"
            size="sm"
            :disabled="!users.prev_page_url"
            @click="router.get(users.prev_page_url)"
          >
            Previous
          </Button>
      
          <!-- Page Numbers -->
          <template v-for="(link, index) in users.links" :key="index">
            <!-- Skip the "Previous" and "Next" text links (we already rendered them as buttons) -->
            <Button
              v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
              :variant="link.active ? 'default' : 'outline'"
              size="sm"
              :disabled="!link.url"
              @click="link.url && router.get(link.url)"
              class="min-w-9"
            >
              {{ link.label }}
            </Button>
          </template>
      
          <!-- Next Button -->
          <Button
            :variant="users.next_page_url ? 'outline' : 'ghost'"
            size="sm"
            :disabled="!users.next_page_url"
            @click="router.get(users.next_page_url)"
          >
            Next
          </Button>
        </nav>
      </div>
    </div>

    <!-- Modals -->
    <AddUserModal
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