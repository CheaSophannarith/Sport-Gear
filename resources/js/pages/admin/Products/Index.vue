<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2, Package } from 'lucide-vue-next';
    import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
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

    interface Product {
        id: number;
        name: string;
        slug: string;
        category: {
            id: number;
            name: string;
        };
        base_price: string;
        featured_image_url: string | null;
        is_featured: boolean;
        is_active: boolean;
        variants_count: number;
        total_stock: number;
        created_at: string;
    }

    interface Props {
        products: {
            data: Product[];
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
            title: 'Products',
            href: ProductController.index.url(),
        },
    ];

    const deleteProduct = (id: number) => {
        router.delete(ProductController.destroy.url(id), {
            preserveScroll: true,
        });
    };

    const formatPrice = (price: string) => {
        return `$${parseFloat(price).toFixed(2)}`;
    };
</script>

<template>

    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Products</CardTitle>
                    <Button as-child>
                        <Link :href="ProductController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Product
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Image</TableHead>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Price</TableHead>
                                    <TableHead>Variants</TableHead>
                                    <TableHead>Stock</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="product in props.products.data" :key="product.id">
                                    <!-- Image -->
                                    <TableCell>
                                        <div class="h-12 w-12 overflow-hidden rounded-md border">
                                            <img v-if="product.featured_image_url" :src="product.featured_image_url"
                                                :alt="product.name" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-muted">
                                                <Package class="h-6 w-6 text-muted-foreground" />
                                            </div>
                                        </div>
                                    </TableCell>

                                    <!-- Product Name -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ product.name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ product.slug }}</span>
                                        </div>
                                    </TableCell>

                                    <!-- Category -->
                                    <TableCell>
                                        <Badge variant="outline">{{ product.category.name }}</Badge>
                                    </TableCell>

                                    <!-- Price -->
                                    <TableCell>
                                        <span class="font-medium">{{ formatPrice(product.base_price) }}</span>
                                    </TableCell>

                                    <!-- Variants -->
                                    <TableCell>
                                        <div class="flex items-center gap-1">
                                            <Badge variant="secondary">{{ product.variants_count }}</Badge>
                                            <span class="text-xs text-muted-foreground">sizes</span>
                                        </div>
                                    </TableCell>

                                    <!-- Stock -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span :class="{
                                                'text-green-600': product.total_stock > 20,
                                                'text-orange-600': product.total_stock > 0 && product.total_stock <= 20,
                                                'text-red-600': product.total_stock === 0
                                            }">
                                                {{ product.total_stock }}
                                            </span>
                                            <span class="text-xs text-muted-foreground">total</span>
                                        </div>
                                    </TableCell>

                                    <!-- Status -->
                                    <TableCell>
                                        <div class="flex flex-col gap-1">
                                            <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                                {{ product.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                            <Badge v-if="product.is_featured" variant="outline" class="border-yellow-600 text-yellow-600">
                                                Featured
                                            </Badge>
                                        </div>
                                    </TableCell>

                                    <!-- Actions -->
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="outline" size="sm" as-child>
                                                <Link :href="ProductController.edit.url(product.id)">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>

                                            <Dialog>
                                                <DialogTrigger as-child>
                                                    <Button variant="destructive" size="sm">
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Delete Product</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to delete "{{ product.name }}"? This action
                                                            cannot be undone.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <div class="flex justify-end gap-4">
                                                        <DialogClose as-child>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <DialogClose as-child>
                                                            <Button variant="destructive" @click="deleteProduct(product.id)">
                                                                Delete
                                                            </Button>
                                                        </DialogClose>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <!-- Empty State -->
                                <TableRow v-if="props.products.data.length === 0">
                                    <TableCell colspan="8" class="text-center py-8">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <Package class="h-12 w-12 text-muted-foreground" />
                                            <p class="text-muted-foreground">No products found</p>
                                            <Button as-child size="sm">
                                                <Link :href="ProductController.create.url()">
                                                    <Plus class="mr-2 h-4 w-4" />
                                                    Create Your First Product
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.products.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (props.products.current_page - 1) * props.products.per_page + 1 }} to
                            {{ Math.min(props.products.current_page * props.products.per_page, props.products.total) }} of
                            {{ props.products.total }} products
                        </div>
                        <div class="flex gap-2">
                            <Button v-for="page in props.products.last_page" :key="page" :variant="page === props.products.current_page ? 'default' : 'outline'" size="sm" as-child>
                                <Link :href="ProductController.index.url({ query: { page } })">
                                    {{ page }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
