<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft } from 'lucide-vue-next';
    import SurfaceTypeController from '@/actions/App/Http/Controllers/Admin/SurfaceTypeController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    interface SurfaceType {
        id: number;
        name: string;
        slug: string;
        code: string | null;
        description: string | null;
        is_active: boolean;
    }

    interface Props {
        surfaceType: SurfaceType;
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
        {
            title: 'Edit',
            href: SurfaceTypeController.edit.url(props.surfaceType.id),
        },
    ];

    const form = useForm({
        name: props.surfaceType.name,
        slug: props.surfaceType.slug,
        code: props.surfaceType.code || '',
        description: props.surfaceType.description || '',
        is_active: Boolean(props.surfaceType.is_active),
    });

    const submit = () => {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
            is_active: data.is_active ? '1' : '0',
        })).post(SurfaceTypeController.update.url(props.surfaceType.id), {
            onSuccess: () => {
                // Form stays populated after successful update
            },
        });
    };
</script>

<template>

    <Head :title="`Edit ${surfaceType.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Edit Surface Type: {{ surfaceType.name }}</CardTitle>
                        <Button variant="outline" as-child>
                            <Link :href="SurfaceTypeController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Surface Types
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <!-- Name -->
                                <div class="space-y-2">
                                    <Label for="name">
                                        Surface Type Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text" placeholder="e.g., Natural Grass"
                                        required />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">
                                        Code
                                        <span class="text-sm text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Input id="code" v-model="form.code" type="text" placeholder="e.g., NG"
                                        maxlength="10" />
                                    <p class="text-xs text-muted-foreground">
                                        Short code for this surface type (max 10 characters)
                                    </p>
                                    <p v-if="form.errors.code" class="text-sm text-destructive">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <!-- Is Active -->
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <!-- Slug -->
                                <div class="space-y-2">
                                    <Label for="slug">
                                        Slug
                                        <span class="text-sm text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Input id="slug" v-model="form.slug" type="text"
                                        placeholder="Auto-generated from name" />
                                    <p class="text-xs text-muted-foreground">
                                        Leave empty to auto-generate from name
                                    </p>
                                    <p v-if="form.errors.slug" class="text-sm text-destructive">
                                        {{ form.errors.slug }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea id="description" v-model="form.description"
                                        placeholder="Surface type description..." rows="4" />
                                    <p v-if="form.errors.description" class="text-sm text-destructive">
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 border-t pt-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="SurfaceTypeController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Surface Type' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
