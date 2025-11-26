<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';



import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Briefcase,
    CalendarCheck,
    CalendarDays,
    CheckSquare,
    FileStack,
    FileText,
    FolderOpen,
    Gavel,
    KanbanSquare,
    LayoutDashboard,
    Settings,
    ShieldCheck,
    UserCheck,
    UserCog,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Case Reports',
        href: '/case_reports',
        icon: FileText,
    },
    {
        title: 'Case By Counsel',
        href: '/cases_by_lawyer_report',
        icon: Gavel,
    },
    {
        title: 'Kanban',
        href: '/task/kanban',
        icon: KanbanSquare,
    },
    {
        title: 'Master Calendar',
        href: '/calender/{start_date}/{end_date}', // You'll need to handle dynamic params
        icon: CalendarDays,
    },
    {
        title: 'My Calendar',
        href: '/calender/{start_date}/{end_date}', // You'll need to handle dynamic params
        icon: CalendarCheck,
    },
    {
        title: 'Tasks',
        href: '/tasks',
        icon: CheckSquare,
    },
    {
        title: 'Files',
        href: '/documents',
        icon: FolderOpen,
    },
    {
        title: 'Case',
        href: '/legal_cases',
        icon: Briefcase,
    },
    {
        title: 'External counsel',
        href: '/external_council',
        icon: UserCheck,
    },
    {
        title: 'Internal counsel',
        href: '/internal_council',
        icon: UserCog,
    },
    {
        title: 'Document templates',
        href: '/knowledge_base/document_templates',
        icon: FileStack,
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: ShieldCheck,
    },
    {
        title: 'Manage',
        href: '/dashboard', // You might want to create a dedicated manage route
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: '#',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
