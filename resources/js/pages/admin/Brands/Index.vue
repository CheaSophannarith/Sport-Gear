<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2 } from 'lucide-vue-next';
    import BrandController from '@/actions/App/Http/Controllers/Admin/BrandController';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogHeader,
        DialogTitle,
        DialogTrigger,
        DialogClose,
    } from '@/components/ui/dialog';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    interface Brand {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        logo: string | null;
        logo_url: string | null;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }

    interface Props {
        brands: {
            data: Brand[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    }

    const props = defineProps<Props>();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
        },
        {
            title: 'Brands',
            href: BrandController.index.url(),
        },
    ];

    const deleteBrand = (id: number) => {
        router.delete(BrandController.destroy.url(id), {
            preserveScroll: true,
        });
    };
</script>

<template>

    <Head title="Brands" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Brands</CardTitle>
                    <Button as-child>
                        <Link :href="BrandController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Brand
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Logo</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="brand in props.brands.data" :key="brand.id">
                                    <TableCell>
                                        <div v-if="brand.logo_url" class="h-12 w-12 overflow-hidden rounded">
                                            <img :src="brand.logo_url" :alt="brand.name"
                                                class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="flex h-12 w-12 items-center justify-center rounded bg-muted">
                                            <span class="text-xs text-muted-foreground">No logo</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ brand.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ brand.slug }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <Badge :variant="brand.is_active ? 'default' : 'secondary'">
                                                {{ brand.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="BrandController.edit.url(brand.id)">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Dialog>
                                                <DialogTrigger>
                                                    <Trash2 class="h-4 w-4 text-destructive" />
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Are you absolutely sure, you want to delete {{
                                                            brand.name }}?</DialogTitle>
                                                        <DialogDescription>
                                                            This action cannot be undone. This will permanently delete
                                                            the brand
                                                            and remove all its data.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <div class="mt-4 flex justify-end gap-2">
                                                        <!-- Cancel button just closes the dialog -->
                                                        <DialogClose asChild>
                                                            <button
                                                                class="text-black px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                                                Cancel
                                                            </button>
                                                        </DialogClose>

                                                        <!-- Delete button triggers your delete function -->
                                                        <button
                                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                                                            @click="deleteBrand(brand.id)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.brands.data.length === 0">
                                    <TableCell colspan="5" class="text-center text-muted-foreground">
                                        No brands found. Click "Add Brand" to create one.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.brands.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ props.brands.data.length }} of {{ props.brands.total }} brands
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="props.brands.current_page === 1"
                                @click="router.get(BrandController.index.url({ query: { page: props.brands.current_page - 1 } }))">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="props.brands.current_page === props.brands.last_page"
                                @click="router.get(BrandController.index.url({ query: { page: props.brands.current_page + 1 } }))">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
