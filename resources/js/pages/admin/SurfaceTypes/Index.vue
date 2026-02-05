<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2 } from 'lucide-vue-next';
    import SurfaceTypeController from '@/actions/App/Http/Controllers/Admin/SurfaceTypeController';
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

    interface SurfaceType {
        id: number;
        name: string;
        slug: string;
        code: string | null;
        description: string | null;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }

    interface Props {
        surfaceTypes: {
            data: SurfaceType[];
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
            title: 'Surface Types',
            href: SurfaceTypeController.index.url(),
        },
    ];

    const deleteSurfaceType = (id: number) => {
        router.delete(SurfaceTypeController.destroy.url(id), {
            preserveScroll: true,
        });
    };
</script>

<template>

    <Head title="Surface Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Surface Types</CardTitle>
                    <Button as-child>
                        <Link :href="SurfaceTypeController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Surface Type
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Code</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="surfaceType in props.surfaceTypes.data" :key="surfaceType.id">
                                    <TableCell class="font-medium">
                                        {{ surfaceType.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ surfaceType.code ?? '-' }}
                                    </TableCell>
                                    <TableCell>
                                        {{ surfaceType.slug }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="max-w-xs truncate" :title="surfaceType.description ?? undefined">
                                            {{ surfaceType.description ?? '-' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <Badge :variant="surfaceType.is_active ? 'default' : 'secondary'">
                                                {{ surfaceType.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="SurfaceTypeController.edit.url(surfaceType.id)">
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
                                                            surfaceType.name }}?</DialogTitle>
                                                        <DialogDescription>
                                                            This action cannot be undone. This will permanently delete
                                                            the surface type
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
                                                            @click="deleteSurfaceType(surfaceType.id)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.surfaceTypes.data.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        No surface types found. Click "Add Surface Type" to create one.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.surfaceTypes.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ props.surfaceTypes.data.length }} of {{ props.surfaceTypes.total }} surface types
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="props.surfaceTypes.current_page === 1"
                                @click="router.get(SurfaceTypeController.index.url({ query: { page: props.surfaceTypes.current_page - 1 } }))">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="props.surfaceTypes.current_page === props.surfaceTypes.last_page"
                                @click="router.get(SurfaceTypeController.index.url({ query: { page: props.surfaceTypes.current_page + 1 } }))">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
