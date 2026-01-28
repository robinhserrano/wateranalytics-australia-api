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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Box, DollarSign, Folder, LayoutGrid, Package, ShoppingCart, Users, Shield, Activity } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

interface ExtendedNavItem extends NavItem {
    roles?: string[];
}

const allNavItems: ExtendedNavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
        roles: ['Admin', 'Sales Manager', 'Sales Team Manager', 'Sales Person'],
    },
    {
        title: 'Sales Orders',
        href: route('sales-orders.index'),
        icon: ShoppingCart,
        roles: ['Admin', 'Sales Manager', 'Sales Team Manager', 'Sales Person'],
    },
    {
        title: 'My Team',
        href: route('my-team.index'),
        icon: Users,
        roles: ['Admin', 'Sales Manager', 'Sales Team Manager'],
    },
    {
        title: 'Stocks',
        href: route('stocks.index'),
        icon: Package,
        roles: ['Admin', 'Sales Manager', 'Sales Team Manager', 'Sales Person'],
    },
    {
        title: 'Commissions',
        href: route('commissions.index'),
        icon: DollarSign,
        roles: ['Admin'],
    },
    {
        title: 'Contacts',
        href: route('contacts.index'),
        icon: Users,
        roles: ['Admin'],
    },
    {
        title: 'Products',
        href: route('products.index'),
        icon: Box,
        roles: ['Admin'],
    },
    {
        title: 'Users',
        href: route('users.index'),
        icon: Users,
        roles: ['Admin'],
    },
    {
        title: 'Teams',
        href: route('teams.index'),
        icon: Users,
        roles: ['Admin'],
    },
    {
        title: 'Roles & Permissions',
        href: route('roles.index'),
        icon: Shield,
        roles: ['Admin'],
    },
    {
        title: 'Odoo Logs',
        href: route('admin.logs.index'),
        icon: Activity,
        roles: ['Admin'],
    },
];

const mainNavItems = computed(() => {
    const userRoles = user.value?.roles?.map((r: any) => r.name) || [];
    
    // If no user or roles, show nothing or basic items? 
    // Assuming auth middleware handles redirect if not logged in.
    
    return allNavItems.filter(item => {
        if (!item.roles) return true; // Accessible to everyone
        return item.roles.some(role => userRoles.includes(role));
    });
});

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
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
