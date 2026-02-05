<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft } from 'lucide-vue-next';
    import { ref } from 'vue';
    import TeamController from '@/actions/App/Http/Controllers/Admin/TeamController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Switch } from '@/components/ui/switch';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';
    import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

    interface League {
        id: number;
        name: string;
    }

    interface Team {
        id: number;
        name: string;
        slug: string;
        league_id: number | null;
        logo: string | null;
        logo_url: string | null;
        is_active: boolean;
    }

    interface Props {
        team: Team;
        leagues: League[];
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
        {
            title: 'Edit',
            href: TeamController.edit.url(props.team.id),
        },
    ];

    const form = useForm({
        name: props.team.name,
        slug: props.team.slug,
        league_id: props.team.league_id,
        logo: null as File | null,
        is_active: Boolean(props.team.is_active),
    });

    const logoPreview = ref<string | null>(
        props.team.logo_url || null
    );

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
        form.transform((data) => {
            const transformed: any = {
                ...data,
                _method: 'PUT',
                is_active: data.is_active ? '1' : '0',
            };

            // Only include logo if a new one was selected
            if (!data.logo) {
                delete transformed.logo;
            }

            return transformed;
        }).post(TeamController.update.url(props.team.id), {
            forceFormData: true,
            onSuccess: () => {
                form.reset('logo');
            },
        });
    };
</script>

<template>

    <Head :title="`Edit ${team.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Edit Team: {{ team.name }}</CardTitle>
                        <Button variant="outline" as-child>
                            <Link :href="TeamController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Teams
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
                                        Team Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text"
                                        placeholder="e.g., Manchester United" required />
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

                                <!-- League -->
                                <div class="space-y-2">
                                    <Label for="league_id">League</Label>
                                    <Select v-model="form.league_id">
                                        <SelectTrigger id="league_id">
                                            <SelectValue placeholder="Select a league" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">No League</SelectItem>
                                            <SelectItem v-for="league in props.leagues" :key="league.id"
                                                :value="league.id">
                                                {{ league.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.league_id" class="text-sm text-destructive">
                                        {{ form.errors.league_id }}
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
                                    <Label for="logo">Team Logo</Label>
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
                                <Link :href="TeamController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Team' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
