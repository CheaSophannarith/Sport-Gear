<script setup lang="ts">
    import { Head, Link, router } from '@inertiajs/vue3';
    import { Edit, Plus, Trash2 } from 'lucide-vue-next';
    import TeamController from '@/actions/App/Http/Controllers/Admin/TeamController';
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

    interface League {
        id: number;
        name: string;
    }

    interface Team {
        id: number;
        name: string;
        slug: string;
        league?: League;
        logo: string | null;
        logo_url: string | null;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    }

    interface Props {
        teams: {
            data: Team[];
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
            title: 'Teams',
            href: TeamController.index.url(),
        },
    ];

    const deleteTeam = (id: number) => {
        router.delete(TeamController.destroy.url(id), {
            preserveScroll: true,
        });
    };
</script>

<template>

    <Head title="Teams" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0">
                    <CardTitle>Teams</CardTitle>
                    <Button as-child>
                        <Link :href="TeamController.create.url()">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Team
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
                                    <TableHead>League</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="team in props.teams.data" :key="team.id">
                                    <TableCell>
                                        <div v-if="team.logo_url" class="h-12 w-12 overflow-hidden rounded">
                                            <img :src="team.logo_url" :alt="team.name"
                                                class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="flex h-12 w-12 items-center justify-center rounded bg-muted">
                                            <span class="text-xs text-muted-foreground">No logo</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ team.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ team.slug }}
                                    </TableCell>
                                    <TableCell>
                                        {{ team.league?.name ?? 'No League' }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="space-y-1">
                                            <Badge :variant="team.is_active ? 'default' : 'secondary'">
                                                {{ team.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="TeamController.edit.url(team.id)">
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
                                                            team.name }}?</DialogTitle>
                                                        <DialogDescription>
                                                            This action cannot be undone. This will permanently delete
                                                            the team
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
                                                            @click="deleteTeam(team.id)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </DialogContent>
                                            </Dialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="props.teams.data.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        No teams found. Click "Add Team" to create one.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.teams.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ props.teams.data.length }} of {{ props.teams.total }} teams
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="props.teams.current_page === 1"
                                @click="router.get(TeamController.index.url({ query: { page: props.teams.current_page - 1 } }))">
                                Previous
                            </Button>
                            <Button variant="outline" size="sm"
                                :disabled="props.teams.current_page === props.teams.last_page"
                                @click="router.get(TeamController.index.url({ query: { page: props.teams.current_page + 1 } }))">
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
