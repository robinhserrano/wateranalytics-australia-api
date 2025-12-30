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
import { Link } from '@inertiajs/vue3';
import { BookOpen, Box, DollarSign, Folder, LayoutGrid, Package, ShoppingCart, Users, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Sales Orders',
        href: route('sales-orders.index'),
        icon: ShoppingCart,
    },
    {
        title: 'Commissions',
        href: route('commissions.index'),
        icon: DollarSign,
    },
    {
        title: 'Contacts',
        href: route('contacts.index'),
        icon: Users,
    },
    {
        title: 'Stocks',
        href: route('stocks.index'),
        icon: Package,
    },
    {
        title: 'Products',
        href: route('products.index'),
        icon: Box,
    },
    {
        title: 'Users',
        href: route('users.index'),
        icon: Users,
    },
    {
        title: 'Teams',
        href: route('teams.index'),
        icon: Users,
    },
    {
        title: 'Roles & Permissions',
        href: route('roles.index'),
        icon: Shield,
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
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
