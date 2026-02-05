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

    interface Category {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        image: string | null;
        sort_order: number;
        is_active: boolean;
    }

    interface Props {
        category: Category;
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
        {
            title: 'Edit',
            href: CategoryController.edit.url(props.category.id),
        },
    ];

    const form = useForm({
        name: props.category.name,
        slug: props.category.slug,
        description: props.category.description || '',
        image: null as File | null,
        sort_order: props.category.sort_order,
        is_active: Boolean(props.category.is_active),
    });

    const imagePreview = ref<string | null>(
        props.category.image ? `/storage/${props.category.image}` : null
    );

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
        form.transform((data) => {
            const transformed: any = {
                ...data,
                _method: 'PUT',
                is_active: data.is_active ? '1' : '0',
            };

            // Only include image if a new one was selected
            if (!data.image) {
                delete transformed.image;
            }

            return transformed;
        }).post(CategoryController.update.url(props.category.id), {
            forceFormData: true,
            onSuccess: () => {
                form.reset('image');
            },
        });
    };
</script>

<template>

    <Head :title="`Edit ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Edit Category: {{ category.name }}</CardTitle>
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
                                    <Switch
                                        id="is_active"
                                        v-model="form.is_active"
                                    />
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
                                {{ form.processing ? 'Updating...' : 'Update Category' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
