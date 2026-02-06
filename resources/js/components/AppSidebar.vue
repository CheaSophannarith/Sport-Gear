<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import { Award, BookOpen, Folder, Image, Layers, LayoutGrid, Package, Shield, Tags, Trophy } from 'lucide-vue-next';
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
    import AppLogo from './AppLogo.vue';

    // Temporary dashboard route until routes are regenerated
    const dashboardRoute = '/admin/dashboard';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardRoute,
            icon: LayoutGrid,
        },
        {
            title: 'Categories',
            href: '/admin/categories',
            icon: Tags,
        },
        {
            title: 'Products',
            href: '/admin/products',
            icon: Package,
        },
        {
            title: 'Brands',
            href: '/admin/brands',
            icon: Award,
        },
        {
            title: 'Leagues',
            href: '/admin/leagues',
            icon: Trophy,
        },
        {
            title: 'Teams',
            href: '/admin/teams',
            icon: Shield,
        },
        {
            title: 'Surface Types',
            href: '/admin/surface-types',
            icon: Layers,
        },
        {
            title: 'Carousels',
            href: '/admin/carousels',
            icon: Image,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Github Repo',
            href: 'https://github.com/laravel/vue-starter-kit',
            icon: Folder,
        },
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
                        <Link :href="dashboardRoute">
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
