<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft } from 'lucide-vue-next';
    import { ref } from 'vue';
    import CarouselController from '@/actions/App/Http/Controllers/Admin/CarouselController';
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
            title: 'Carousels',
            href: CarouselController.index.url(),
        },
        {
            title: 'Create',
            href: CarouselController.create.url(),
        },
    ];

    const form = useForm({
        title: '',
        link: '',
        description: '',
        image: null as File | null,
        order: 0,
        is_active: true,
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
        })).post(CarouselController.store.url(), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
            },
        });
    };
</script>

<template>

    <Head title="Create Carousel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-2 sm:p-4">
            <Card>
                <CardHeader>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <CardTitle class="text-xl sm:text-2xl">Create New Carousel</CardTitle>
                        <Button variant="outline" as-child class="w-full sm:w-auto">
                            <Link :href="CarouselController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Carousels
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <!-- Left Column - Form Fields -->
                            <div class="space-y-4 order-2 lg:order-1">
                                <!-- Title -->
                                <div class="space-y-2">
                                    <Label for="title">
                                        Carousel Title
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="title" v-model="form.title" type="text"
                                        placeholder="e.g., Summer Sale 2024"
                                        required />
                                    <p v-if="form.errors.title" class="text-sm text-destructive">
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Link -->
                                <div class="space-y-2">
                                    <Label for="link">
                                        Link URL
                                        <span class="text-sm text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Input id="link" v-model="form.link" type="url"
                                        placeholder="https://example.com/sale" />
                                    <p class="text-xs text-muted-foreground">
                                        Where users will be redirected when clicking the carousel
                                    </p>
                                    <p v-if="form.errors.link" class="text-sm text-destructive">
                                        {{ form.errors.link }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">
                                        Description
                                        <span class="text-sm text-muted-foreground">(Optional)</span>
                                    </Label>
                                    <Textarea id="description" v-model="form.description"
                                        placeholder="Brief description of the carousel..."
                                        rows="3" />
                                    <p v-if="form.errors.description" class="text-sm text-destructive">
                                        {{ form.errors.description }}
                                    </p>
                                </div>

                                <!-- Display Order -->
                                <div class="space-y-2">
                                    <Label for="order">Display Order</Label>
                                    <Input id="order" v-model.number="form.order" type="number" min="0"
                                        placeholder="0" />
                                    <p class="text-xs text-muted-foreground">
                                        Lower numbers appear first (0 = highest priority)
                                    </p>
                                    <p v-if="form.errors.order" class="text-sm text-destructive">
                                        {{ form.errors.order }}
                                    </p>
                                </div>

                                <!-- Is Active -->
                                <div class="flex items-center space-x-2 p-4 rounded-lg border bg-muted/50">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <div class="flex-1">
                                        <Label for="is_active" class="cursor-pointer">
                                            Active Status
                                        </Label>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            Only active carousels will be displayed on the website
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Image Upload -->
                            <div class="space-y-4 order-1 lg:order-2">
                                <div class="space-y-2">
                                    <Label for="image">
                                        Carousel Image
                                        <span class="text-sm text-muted-foreground">(Recommended: 1920x600px)</span>
                                    </Label>

                                    <!-- Image Preview -->
                                    <div class="relative">
                                        <div v-if="imagePreview"
                                            class="relative aspect-video overflow-hidden rounded-lg border bg-muted">
                                            <img :src="imagePreview" alt="Preview"
                                                class="h-full w-full object-cover" />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <p class="text-white text-sm">Preview</p>
                                            </div>
                                        </div>
                                        <div v-else
                                            class="flex aspect-video items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 bg-muted/50">
                                            <div class="text-center p-4">
                                                <svg class="mx-auto h-12 w-12 text-muted-foreground/50" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <p class="mt-2 text-sm text-muted-foreground">No image selected</p>
                                                <p class="text-xs text-muted-foreground mt-1">Click below to upload</p>
                                            </div>
                                        </div>
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

                        <!-- Form Actions -->
                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 border-t pt-6">
                            <Button variant="outline" type="button" as-child class="w-full sm:w-auto">
                                <Link :href="CarouselController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="w-full sm:w-auto">
                                {{ form.processing ? 'Creating...' : 'Create Carousel' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
