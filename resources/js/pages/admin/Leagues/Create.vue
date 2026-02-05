<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft } from 'lucide-vue-next';
    import { ref } from 'vue';
    import LeagueController from '@/actions/App/Http/Controllers/Admin/LeagueController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Switch } from '@/components/ui/switch';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
        },
        {
            title: 'Leagues',
            href: LeagueController.index.url(),
        },
        {
            title: 'Create',
            href: LeagueController.create.url(),
        },
    ];

    const form = useForm({
        name: '',
        slug: '',
        country: '',
        logo: null as File | null,
        is_active: false,
    });

    const logoPreview = ref<string | null>(null);

    const handleLogoChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const file = target.files?.[0];
        if (file) {
            form.logo = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                logoPreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(file);
        }
    };

    const submit = () => {
        form.transform((data) => ({
            ...data,
            is_active: data.is_active ? '1' : '0',
        })).post(LeagueController.store.url(), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
            },
        });
    };
</script>

<template>

    <Head title="Create League" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Create New League</CardTitle>
                        <Button variant="outline" as-child>
                            <Link :href="LeagueController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Leagues
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
                                        League Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text" placeholder="e.g., Premier League"
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

                                <!-- Country -->
                                <div class="space-y-2">
                                    <Label for="country">Country</Label>
                                    <Input id="country" v-model="form.country" type="text" placeholder="e.g., England" />
                                    <p v-if="form.errors.country" class="text-sm text-destructive">
                                        {{ form.errors.country }}
                                    </p>
                                </div>

                                <!-- Is Active -->
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                            </div>

                            <!-- Right Column - Logo Upload -->
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="logo">League Logo</Label>
                                    <div class="flex flex-col gap-4">
                                        <!-- Logo Preview -->
                                        <div v-if="logoPreview"
                                            class="relative aspect-video overflow-hidden rounded-lg border">
                                            <img :src="logoPreview" alt="Preview" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else
                                            class="flex aspect-video items-center justify-center rounded-lg border border-dashed">
                                            <span class="text-sm text-muted-foreground">No logo selected</span>
                                        </div>

                                        <!-- File Input -->
                                        <Input id="logo" type="file" accept="image/*" @change="handleLogoChange" />
                                        <p class="text-xs text-muted-foreground">
                                            Accepted formats: JPEG, PNG, JPG, GIF, WEBP (max 2MB)
                                        </p>
                                        <p v-if="form.errors.logo" class="text-sm text-destructive">
                                            {{ form.errors.logo }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 border-t pt-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="LeagueController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create League' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
