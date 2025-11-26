<script setup lang="ts">
// Page props coming from Laravel controller
interface Props {
    lawyers: Array<any>;
}

defineProps<Props>();

// Layouts & Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

// UI components
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';


</script>

<template>
    <AppLayout>
        <Head title="Case Reports" />

        <div class="flex flex-col space-y-8">
            <!-- Header Section -->
            <HeadingSmall
                title="Case Reports"
                description="View consolidated reports and contingent liability summaries"
            />

            <!-- Reports Table -->
            <Card class="rounded-xl border shadow-sm">
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Case Title</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Updated</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="(item, index) in lawyers"
                                :key="index"
                            >
                                <TableCell>{{ item.id }}</TableCell>
                                <TableCell>{{ item.title }}</TableCell>

                                <TableCell>
                                    <Badge variant="outline">
                                        {{ item.status }}
                                    </Badge>
                                </TableCell>

                                <TableCell>
                                    {{ Number(item.amount).toLocaleString() }}
                                </TableCell>

                                <TableCell>
                                    {{ item.updated_at }}
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="lawyers.length === 0">
                                <TableCell
                                    colspan="5"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No case reports found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
