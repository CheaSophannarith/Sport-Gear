<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2 } from 'lucide-vue-next';
    import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
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

    interface Category {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        image: string | null;
        sort_order: number;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }

    interface Props {
        categories: {
            data: Category[];
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
            title: 'Categories',
            href: CategoryController.index.url(),
        },
    ];

    const deleteCategory = (id: number) => {
        router.delete(CategoryController.destroy.url(id), {
            preserveScroll: true,
        });
    };
</script>

<template>

    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Categories</CardTitle>
                    <Button as-child>
                        <Link :href="CategoryController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Category
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Image</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Sort Order</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="category in props.categories.data" :key="category.id">
                                    <TableCell>
                                        <div v-if="category.image" class="h-12 w-12 overflow-hidden rounded">
                                            <img :src="`/storage/${category.image}`" :alt="category.name"
                                                class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="flex h-12 w-12 items-center justify-center rounded bg-muted">
                                            <span class="text-xs text-muted-foreground">No image</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ category.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ category.slug }}
                                    </TableCell>
                                    <TableCell>
                                        {{ category.sort_order }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                                {{ category.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="CategoryController.edit.url(category.id)">
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
                                                            category.name }}?</DialogTitle>
                                                        <DialogDescription>
                                                            This action cannot be undone. This will permanently delete
                                                            the category
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
                                                            @click="deleteCategory(category.id)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.categories.data.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        No categories found. Click "Add Category" to create one.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.categories.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ props.categories.data.length }} of {{ props.categories.total }} categories
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="props.categories.current_page === 1"
                                @click="router.get(CategoryController.index.url({ query: { page: props.categories.current_page - 1 } }))">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="props.categories.current_page === props.categories.last_page"
                                @click="router.get(CategoryController.index.url({ query: { page: props.categories.current_page + 1 } }))">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
