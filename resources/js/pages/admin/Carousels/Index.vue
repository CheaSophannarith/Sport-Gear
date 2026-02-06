<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2 } from 'lucide-vue-next';
    import CarouselController from '@/actions/App/Http/Controllers/Admin/CarouselController';
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

    interface Carousel {
        id: number;
        title: string;
        link: string | null;
        description: string | null;
        image: string | null;
        image_url: string | null;
        order: number;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }

    interface Props {
        carousels: {
            data: Carousel[];
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
            title: 'Carousels',
            href: CarouselController.index.url(),
        },
    ];

    const deleteCarousel = (id: number) => {
        router.delete(CarouselController.destroy.url(id), {
            preserveScroll: true,
        });
    };
</script>

<template>

    <Head title="Carousels" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-2 sm:p-4">
            <Card>
                <CardHeader class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <CardTitle class="text-xl sm:text-2xl">Carousel Management</CardTitle>
                    <Button as-child class="w-full sm:w-auto">
                        <Link :href="CarouselController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Carousel
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <!-- Mobile & Tablet View (Card Grid) - Hidden on large screens -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:hidden">
                        <Card v-for="carousel in props.carousels.data" :key="carousel.id" class="overflow-hidden">
                            <div class="relative">
                                <!-- Image -->
                                <div v-if="carousel.image_url" class="aspect-video overflow-hidden bg-muted">
                                    <img :src="carousel.image_url" :alt="carousel.title"
                                        class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="flex aspect-video items-center justify-center bg-muted">
                                    <span class="text-sm text-muted-foreground">No image</span>
                                </div>

                                <!-- Order Badge -->
                                <div class="absolute top-2 left-2">
                                    <Badge variant="secondary" class="bg-white/90 backdrop-blur">
                                        Order: {{ carousel.order }}
                                    </Badge>
                                </div>
                            </div>

                            <CardContent class="p-4 space-y-3">
                                <!-- Title -->
                                <h3 class="font-semibold text-lg line-clamp-2">{{ carousel.title }}</h3>

                                <!-- Description -->
                                <p v-if="carousel.description" class="text-sm text-muted-foreground line-clamp-2">
                                    {{ carousel.description }}
                                </p>

                                <!-- Link -->
                                <div v-if="carousel.link" class="text-xs text-muted-foreground truncate">
                                    <span class="font-medium">Link:</span> {{ carousel.link }}
                                </div>

                                <!-- Status & Actions -->
                                <div class="flex items-center justify-between pt-2 border-t">
                                    <Badge :variant="carousel.is_active ? 'default' : 'secondary'">
                                        {{ carousel.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>

                                    <div class="flex gap-2">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="CarouselController.edit.url(carousel.id)">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Dialog>
                                            <DialogTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <Trash2 class="h-4 w-4 text-destructive" />
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Delete Carousel</DialogTitle>
                                                    <DialogDescription>
                                                        Are you sure you want to delete "{{ carousel.title }}"?
                                                        This action cannot be undone.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
                                                    <DialogClose asChild>
                                                        <Button variant="outline" class="w-full sm:w-auto">
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>
                                                    <Button variant="destructive" @click="deleteCarousel(carousel.id)"
                                                        class="w-full sm:w-auto">
                                                        Delete
                                                    </Button>
                                                </div>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Empty State for Mobile -->
                        <div v-if="props.carousels.data.length === 0"
                            class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <p class="text-muted-foreground mb-4">No carousels found.</p>
                            <Button as-child>
                                <Link :href="CarouselController.create.url()">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Create Your First Carousel
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Desktop View (Table) - Hidden on small/medium screens -->
                    <div class="hidden lg:block rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[100px]">Order</TableHead>
                                    <TableHead class="w-[200px]">Image</TableHead>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Link</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="carousel in props.carousels.data" :key="carousel.id">
                                    <TableCell>
                                        <Badge variant="outline">{{ carousel.order }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="carousel.image_url" class="h-16 w-28 overflow-hidden rounded">
                                            <img :src="carousel.image_url" :alt="carousel.title"
                                                class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="flex h-16 w-28 items-center justify-center rounded bg-muted">
                                            <span class="text-xs text-muted-foreground">No image</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="max-w-xs">
                                            <p class="font-medium">{{ carousel.title }}</p>
                                            <p v-if="carousel.description" class="text-sm text-muted-foreground truncate">
                                                {{ carousel.description }}
                                            </p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <a v-if="carousel.link" :href="carousel.link" target="_blank"
                                            class="text-sm text-primary hover:underline truncate block max-w-xs">
                                            {{ carousel.link }}
                                        </a>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="carousel.is_active ? 'default' : 'secondary'">
                                            {{ carousel.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="CarouselController.edit.url(carousel.id)">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Dialog>
                                                <DialogTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <Trash2 class="h-4 w-4 text-destructive" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>Delete Carousel</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to delete "{{ carousel.title }}"?
                                                            This action cannot be undone.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    <div class="mt-4 flex justify-end gap-2">
                                                        <DialogClose asChild>
                                                            <Button variant="outline">Cancel</Button>
                                                        </DialogClose>
                                                        <Button variant="destructive" @click="deleteCarousel(carousel.id)">
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.carousels.data.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                        <div class="flex flex-col items-center gap-4">
                                            <p>No carousels found.</p>
                                            <Button as-child>
                                                <Link :href="CarouselController.create.url()">
                                                    <Plus class="mr-2 h-4 w-4" />
                                                    Create Your First Carousel
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.carousels.last_page > 1" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-muted-foreground text-center sm:text-left">
                            Showing {{ props.carousels.data.length }} of {{ props.carousels.total }} carousels
                        </div>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <Button variant="outline" size="sm" :disabled="props.carousels.current_page === 1"
                                @click="router.get(CarouselController.index.url({ query: { page: props.carousels.current_page - 1 } }))"
                                class="flex-1 sm:flex-none">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="props.carousels.current_page === props.carousels.last_page"
                                @click="router.get(CarouselController.index.url({ query: { page: props.carousels.current_page + 1 } }))"
                                class="flex-1 sm:flex-none">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
