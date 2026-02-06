<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { ArrowLeft, Plus, Trash2, ChevronUp, ChevronDown } from 'lucide-vue-next';
    import { ref } from 'vue';
    import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    interface FilterType {
        value: string;
        label: string;
    }

    interface Filter {
        type: string;
        required: boolean;
        sort_order: number;
    }

    interface VariantSize {
        value: string;
        label: string;
    }

    interface Category {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        image: string | null;
        image_url: string | null;
        sort_order: number;
        is_active: boolean;
        filters?: Filter[];
        variant_sizes?: VariantSize[];
    }

    interface Props {
        category: Category;
        availableFilters: FilterType[];
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
        filters: (props.category.filters || []) as Filter[],
        variant_sizes: (props.category.variant_sizes || []) as VariantSize[],
    });

    const imagePreview = ref<string | null>(
        props.category.image_url || null
    );

    const newSizeValue = ref('');
    const newSizeLabel = ref('');

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

    const toggleFilter = (filterValue: string, checked: boolean) => {

        const index = form.filters.findIndex((f) => f.type === filterValue);

        if (checked && index === -1) {
            // Add filter if checked and not already in array
            form.filters.push({
                type: filterValue,
                required: false,
                sort_order: form.filters.length + 1,
            });
            
        } else if (!checked && index > -1) {
            // Remove filter if unchecked and exists in array
            form.filters.splice(index, 1);
            // Recalculate sort_order for remaining filters
            form.filters.forEach((filter, idx) => {
                filter.sort_order = idx + 1;
            });
        }
    };

    const isFilterSelected = (filterValue: string) => {
        return form.filters.some((f) => f.type === filterValue);
    };

    const moveFilterUp = (filterValue: string) => {
        const index = form.filters.findIndex((f) => f.type === filterValue);
        if (index > 0) {
            const temp = form.filters[index];
            form.filters[index] = form.filters[index - 1];
            form.filters[index - 1] = temp;
            // Update sort_order
            form.filters.forEach((filter, idx) => {
                filter.sort_order = idx + 1;
            });
        }
    };

    const moveFilterDown = (filterValue: string) => {
        const index = form.filters.findIndex((f) => f.type === filterValue);
        if (index < form.filters.length - 1) {
            const temp = form.filters[index];
            form.filters[index] = form.filters[index + 1];
            form.filters[index + 1] = temp;
            // Update sort_order
            form.filters.forEach((filter, idx) => {
                filter.sort_order = idx + 1;
            });
        }
    };

    const getFilterLabel = (filterValue: string) => {
        return props.availableFilters.find(f => f.value === filterValue)?.label || filterValue;
    };

    const isFilterRequired = (filterValue: string): boolean => {
        const filter = form.filters.find(f => f.type === filterValue);
        return filter ? Boolean(filter.required) : false;
    };

    const toggleFilterRequired = (filterValue: string, checked: boolean) => {
        // Create a new array with new objects to properly trigger Inertia's reactivity
        form.filters = form.filters.map((filter) =>
            filter.type === filterValue
                ? { ...filter, required: checked }
                : filter
        );
    };

    const addVariantSize = () => {
        if (newSizeValue.value && newSizeLabel.value) {
            form.variant_sizes.push({
                value: newSizeValue.value.trim(),
                label: newSizeLabel.value.trim(),
            });
            newSizeValue.value = '';
            newSizeLabel.value = '';
        }
    };

    const removeVariantSize = (index: number) => {
        form.variant_sizes.splice(index, 1);
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

                        <!-- Category Filters Section -->
                        <div class="space-y-4 border-t pt-6">
                            <div class="space-y-2">
                                <Label class="text-lg font-semibold">Category Filters</Label>
                                <p class="text-sm text-muted-foreground">
                                    Select which filters apply to products in this category
                                </p>
                            </div>

                            <!-- Selected Filters (with ordering) -->
                            <div v-if="form.filters.length > 0" class="space-y-3">
                                <div class="text-sm font-medium">Active Filters</div>
                                <div class="space-y-2">
                                    <div v-for="(filter, index) in form.filters" :key="`${filter.type}-${filter.required}`"
                                        class="flex items-center gap-3 rounded-md border border-primary/50 bg-primary/5 p-4">
                                        <!-- Order Controls -->
                                        <div class="flex flex-col gap-1">
                                            <Button type="button" variant="ghost" size="sm"
                                                class="h-5 w-5 p-0 hover:bg-primary/20"
                                                :disabled="index === 0"
                                                @click="moveFilterUp(filter.type)">
                                                <ChevronUp class="h-3 w-3" />
                                            </Button>
                                            <Button type="button" variant="ghost" size="sm"
                                                class="h-5 w-5 p-0 hover:bg-primary/20"
                                                :disabled="index === form.filters.length - 1"
                                                @click="moveFilterDown(filter.type)">
                                                <ChevronDown class="h-3 w-3" />
                                            </Button>
                                        </div>

                                        <!-- Filter Info -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                                                    {{ index + 1 }}
                                                </span>
                                                <span class="font-medium">{{ getFilterLabel(filter.type) }}</span>
                                            </div>
                                        </div>

                                        <!-- Required Checkbox -->
                                        <div class="flex items-center space-x-2">
                                            <input
                                                type="checkbox"
                                                :id="`filter-required-${filter.type}`"
                                                :checked="isFilterRequired(filter.type)"
                                                @change="(e) => toggleFilterRequired(filter.type, (e.target as HTMLInputElement).checked)"
                                                class="filter-required-checkbox h-4 w-4 rounded border-input cursor-pointer"
                                            />
                                            <Label :for="`filter-required-${filter.type}`"
                                                class="text-sm font-normal cursor-pointer">
                                                Required
                                            </Label>
                                        </div>

                                        <!-- Remove Button -->
                                        <Button type="button" variant="ghost" size="sm"
                                            class="hover:bg-destructive/20"
                                            @click="toggleFilter(filter.type, false)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Filters (not selected) -->
                            <div class="space-y-3">
                                <div class="text-sm font-medium">Available Filters</div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div v-for="filter in availableFilters.filter(f => !isFilterSelected(f.value))"
                                        :key="filter.value"
                                        class="flex items-center space-x-3 rounded-md border border-dashed p-4 hover:border-primary/50 hover:bg-accent/50 transition-colors cursor-pointer"
                                        @click="toggleFilter(filter.value, true)">
                                        <Checkbox :id="`filter-${filter.value}`"
                                            :checked="false"
                                            @click.stop="toggleFilter(filter.value, true)" />
                                        <Label :for="`filter-${filter.value}`" class="font-medium cursor-pointer flex-1">
                                            {{ filter.label }}
                                        </Label>
                                    </div>
                                </div>
                                <p v-if="availableFilters.filter(f => !isFilterSelected(f.value)).length === 0"
                                    class="text-sm text-muted-foreground italic">
                                    All available filters have been added.
                                </p>
                            </div>
                        </div>

                        <!-- Variant Sizes Section -->
                        <div class="space-y-4 border-t pt-6">
                            <div class="space-y-2">
                                <Label class="text-lg font-semibold">Variant Sizes</Label>
                                <p class="text-sm text-muted-foreground">
                                    Define available sizes for products in this category (e.g., 39, 40, 41 for boots or
                                    S, M, L for jerseys)
                                </p>
                            </div>

                            <!-- Add Size Form -->
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <Input v-model="newSizeValue" placeholder="Size value (e.g., 39, M)"
                                        @keyup.enter="addVariantSize" />
                                </div>
                                <div class="flex-1">
                                    <Input v-model="newSizeLabel" placeholder="Display label (e.g., EU 39, Medium)"
                                        @keyup.enter="addVariantSize" />
                                </div>
                                <Button type="button" @click="addVariantSize"
                                    :disabled="!newSizeValue || !newSizeLabel">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Add Size
                                </Button>
                            </div>

                            <!-- Sizes List -->
                            <div v-if="form.variant_sizes.length > 0" class="space-y-2">
                                <div v-for="(size, index) in form.variant_sizes" :key="index"
                                    class="flex items-center justify-between rounded-md border p-3">
                                    <div class="flex-1">
                                        <span class="font-medium">{{ size.value }}</span>
                                        <span class="text-muted-foreground"> - {{ size.label }}</span>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" @click="removeVariantSize(index)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground italic">
                                No sizes added yet. Add sizes that will be available for products in this category.
                            </p>
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

<style scoped>
.filter-required-checkbox {
    appearance: none;
    -webkit-appearance: none;
    background-color: transparent;
    border: 1px solid #d1d5db; /* gray-300 */
    position: relative;
}

.filter-required-checkbox:checked {
    background-color: white;
    border-color: hsl(var(--primary));
}

.filter-required-checkbox:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: black;
    font-size: 12px;
    font-weight: bold;
}

.filter-required-checkbox:focus {
    outline: 2px solid hsl(var(--ring) / 0.5);
    outline-offset: 2px;
}
</style>
