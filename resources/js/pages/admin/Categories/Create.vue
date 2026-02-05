<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft } from 'lucide-vue-next';
    import { ref } from 'vue';
    import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
        },
        {
            title: 'Categories',
            href: CategoryController.index.url(),
        },
        {
            title: 'Create',
            href: CategoryController.create.url(),
        },
    ];

    const form = useForm({
        name: '',
        slug: '',
        description: '',
        image: null as File | null,
        sort_order: 0,
        is_active: false,
    });

    const imagePreview = ref<string | null>(null);

    const handleImageChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const file = target.files?.[0];
        if (file) {
            form.image = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(file);
        }
    };

    const submit = () => {
        form.transform((data) => ({
            ...data,
            is_active: data.is_active ? '1' : '0',
        })).post(CategoryController.store.url(), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
            },
        });
    };
</script>

<template>

    <Head title="Create Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Create New Category</CardTitle>
                        <Button variant="outline" as-child>
                            <Link :href="CategoryController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Categories
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
                                        Category Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text" placeholder="e.g., Football"
                                        required />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

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
                                        placeholder="Category description..." rows="4" />
                                    <p v-if="form.errors.description" class="text-sm text-destructive">
                                        {{ form.errors.description }}
                                    </p>
                                </div>

                                <!-- Sort Order -->
                                <div class="space-y-2">
                                    <Label for="sort_order">Sort Order</Label>
                                    <Input id="sort_order" v-model.number="form.sort_order" type="number" min="0" />
                                    <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                                        {{ form.errors.sort_order }}
                                    </p>
                                </div>

                                <!-- Is Active -->
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                            </div>

                            <!-- Right Column - Image Upload -->
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="image">Category Image</Label>
                                    <div class="flex flex-col gap-4">
                                        <!-- Image Preview -->
                                        <div v-if="imagePreview"
                                            class="relative aspect-video overflow-hidden rounded-lg border">
                                            <img :src="imagePreview" alt="Preview" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else
                                            class="flex aspect-video items-center justify-center rounded-lg border border-dashed">
                                            <span class="text-sm text-muted-foreground">No image selected</span>
                                        </div>

                                        <!-- File Input -->
                                        <Input id="image" type="file" accept="image/*" @change="handleImageChange" />
                                        <p class="text-xs text-muted-foreground">
                                            Accepted formats: JPEG, PNG, JPG, GIF, WEBP (max 2MB)
                                        </p>
                                        <p v-if="form.errors.image" class="text-sm text-destructive">
                                            {{ form.errors.image }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 border-t pt-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="CategoryController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Category' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
