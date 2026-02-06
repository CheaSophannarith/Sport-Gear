<script setup lang="ts">
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import axios from 'axios';
    import { ArrowLeft, Plus, X } from 'lucide-vue-next';
    import { ref, watch, computed, onMounted } from 'vue';
    import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select';
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';

    interface Category {
        id: number;
        name: string;
    }

    interface FilterOption {
        id: number;
        name: string;
        logo_url?: string;
        league_id?: number;
    }

    interface CategoryDetails {
        id: number;
        name: string;
        filters: Array<{
            type: string;
            required: boolean;
            sort_order: number;
        }>;
        variant_sizes: Array<{
            value: string;
            label: string;
            sort_order: number;
        }>;
    }

    interface Variant {
        id?: number;
        size: string;
        sku: string;
        price_adjustment: number;
        stock_quantity: number;
        low_stock_threshold: number;
        is_active: boolean;
    }

    interface ExistingImage {
        id: number;
        url: string;
        name: string;
    }

    interface Product {
        id: number;
        category_id: number;
        name: string;
        slug: string;
        description: string | null;
        features: string[];
        base_price: string;
        featured_image_url: string | null;
        images_urls: ExistingImage[];
        is_featured: boolean;
        is_active: boolean;
        brand_id: number | null;
        league_id: number | null;
        team_id: number | null;
        surface_type_id: number | null;
        variants: Variant[];
    }

    interface Props {
        product: Product;
        categories: Category[];
    }

    const props = defineProps<Props>();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
        },
        {
            title: 'Products',
            href: ProductController.index.url(),
        },
        {
            title: 'Edit',
            href: ProductController.edit.url(props.product.id),
        },
    ];

    const form = useForm({
        category_id: props.product.category_id,
        name: props.product.name,
        slug: props.product.slug,
        description: props.product.description || '',
        features: props.product.features || [],
        base_price: parseFloat(props.product.base_price),
        featured_image: null as File | null,
        images: [] as File[],
        delete_images: [] as number[],
        brand_id: props.product.brand_id || null,
        league_id: props.product.league_id || null,
        team_id: props.product.team_id || null,
        surface_type_id: props.product.surface_type_id || null,
        variants: props.product.variants.map(v => ({
            id: v.id,
            size: v.size,
            sku: v.sku || '',
            price_adjustment: parseFloat(v.price_adjustment.toString()),
            stock_quantity: v.stock_quantity,
            low_stock_threshold: v.low_stock_threshold,
            is_active: v.is_active,
        })),
        is_featured: props.product.is_featured,
        is_active: props.product.is_active,
    });

    const showValidationErrors = ref(false);
    const categoryDetails = ref<CategoryDetails | null>(null);
    const brands = ref<FilterOption[]>([]);
    const leagues = ref<FilterOption[]>([]);
    const teams = ref<FilterOption[]>([]);
    const surfaceTypes = ref<FilterOption[]>([]);
    const featuredImagePreview = ref<string | null>(props.product.featured_image_url);
    const existingImages = ref<ExistingImage[]>(props.product.images_urls || []);
    const imagePreviews = ref<string[]>([]);
    const newFeature = ref('');

    // Computed: Available teams based on selected leagues
    const availableTeams = computed(() => {
        if (!form.league_id) {
            return teams.value;
        }
        return teams.value.filter(team => team.league_id === form.league_id);
    });

    // Load initial category details on mount
    onMounted(async () => {
        await loadCategoryDetails(form.category_id);
    });

    // Load category details when category changes
    watch(() => form.category_id, async (newCategoryId) => {
        if (!newCategoryId) {
            categoryDetails.value = null;
            return;
        }
        await loadCategoryDetails(newCategoryId);
    });

    // Load teams when league changes (cascading filter)
    watch(() => form.league_id, async (newLeagueId) => {
        if (!newLeagueId) {
            form.team_id = null;
            return;
        }

        try {
            const response = await axios.get(`/api/admin/teams?league_id=${newLeagueId}`);
            teams.value = response.data;

            // Clear team selection if it doesn't belong to new league
            if (form.team_id && !teams.value.some(t => t.id === form.team_id)) {
                form.team_id = null;
            }
        } catch (error) {
            console.error('Failed to load teams:', error);
        }
    });

    const loadCategoryDetails = async (categoryId: number) => {
        try {
            const response = await axios.get(`/api/admin/categories/${categoryId}`);
            categoryDetails.value = response.data;

            // Load filter options based on category filters
            if (categoryDetails.value!.filters.some(f => f.type === 'brand')) {
                await loadBrands();
            }
            if (categoryDetails.value!.filters.some(f => f.type === 'league')) {
                await loadLeagues();
            }
            if (categoryDetails.value!.filters.some(f => f.type === 'team')) {
                await loadTeams();
            }
            if (categoryDetails.value!.filters.some(f => f.type === 'surface_type')) {
                await loadSurfaceTypes();
            }

            // Sync variants with category sizes if they've changed
            syncVariantsWithCategorySizes();
        } catch (error) {
            console.error('Failed to load category details:', error);
        }
    };

    const syncVariantsWithCategorySizes = () => {
        if (!categoryDetails.value) return;

        const categorySizes = categoryDetails.value.variant_sizes.map(s => s.value);
        const existingVariants = form.variants;

        // Add new sizes that don't exist
        categorySizes.forEach(size => {
            if (!existingVariants.some(v => v.size === size)) {
                form.variants.push({
                    id: undefined,
                    size,
                    sku: '',
                    price_adjustment: 0,
                    stock_quantity: 0,
                    low_stock_threshold: 5,
                    is_active: true,
                });
            }
        });

        // Remove variants for sizes no longer in category
        form.variants = form.variants.filter(v => categorySizes.includes(v.size));
    };

    const loadBrands = async () => {
        try {
            const response = await axios.get('/api/admin/filters/brands');
            brands.value = response.data;
        } catch (error) {
            console.error('Failed to load brands:', error);
        }
    };

    const loadLeagues = async () => {
        try {
            const response = await axios.get('/api/admin/filters/leagues');
            leagues.value = response.data;
        } catch (error) {
            console.error('Failed to load leagues:', error);
        }
    };

    const loadTeams = async () => {
        try {
            const response = await axios.get('/api/admin/teams');
            teams.value = response.data;
        } catch (error) {
            console.error('Failed to load teams:', error);
        }
    };

    const loadSurfaceTypes = async () => {
        try {
            const response = await axios.get('/api/admin/filters/surface-types');
            surfaceTypes.value = response.data;
        } catch (error) {
            console.error('Failed to load surface types:', error);
        }
    };

    const handleFeaturedImageChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const file = target.files?.[0];
        if (file) {
            form.featured_image = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                featuredImagePreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(file);
        }
    };

    const handleImagesChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const files = Array.from(target.files || []);
        if (files.length > 0) {
            form.images.push(...files);
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreviews.value.push(e.target?.result as string);
                };
                reader.readAsDataURL(file);
            });
        }
    };

    const removeNewImage = (index: number) => {
        form.images.splice(index, 1);
        imagePreviews.value.splice(index, 1);
    };

    const removeExistingImage = (imageId: number, index: number) => {
        form.delete_images.push(imageId);
        existingImages.value.splice(index, 1);
    };

    const addFeature = () => {
        if (newFeature.value.trim()) {
            form.features.push(newFeature.value.trim());
            newFeature.value = '';
        }
    };

    const removeFeature = (index: number) => {
        form.features.splice(index, 1);
    };

    const hasFilter = (filterType: string): boolean => {
        return categoryDetails.value?.filters.some(f => f.type === filterType) ?? false;
    };

    const isFilterRequired = (filterType: string): boolean => {
        return categoryDetails.value?.filters.find(f => f.type === filterType)?.required ?? false;
    };

    const getSizeLabel = (sizeValue: string): string => {
        return categoryDetails.value?.variant_sizes.find(s => s.value === sizeValue)?.label ?? sizeValue;
    };

    const calculateFinalPrice = (basePrice: number, adjustment: number): number => {
        return basePrice + adjustment;
    };

    const submit = () => {
        showValidationErrors.value = true;

        // Transform data for proper FormData handling
        form.transform((data) => ({
            ...data,
            // Convert booleans to '1' or '0' for FormData
            is_featured: data.is_featured ? '1' : '0',
            is_active: data.is_active ? '1' : '0',
            // Ensure proper variant boolean conversion
            variants: data.variants.map(v => ({
                ...v,
                is_active: v.is_active ? '1' : '0'
            }))
        })).put(ProductController.update.url(props.product.id), {
            forceFormData: true,
            preserveScroll: true,
            onError: (errors) => {
                console.error('Validation errors:', errors);
            },
            onSuccess: () => {
                showValidationErrors.value = false;
            }
        });
    };
</script>

<template>

    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Edit Product: {{ product.name }}</CardTitle>
                        <Button variant="outline" as-child>
                            <Link :href="ProductController.index.url()">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Products
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Global Error Message -->
                    <div v-if="Object.keys(form.errors).length > 0 && showValidationErrors"
                        class="mb-4 rounded-md border border-destructive bg-destructive/10 p-4">
                        <h4 class="text-sm font-semibold text-destructive mb-2">Please fix the following errors:</h4>
                        <ul class="list-disc list-inside text-sm text-destructive space-y-1">
                            <li v-for="(error, field) in form.errors" :key="field">
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Basic Information</h3>
                            <div class="grid gap-6 md:grid-cols-2">
                                <!-- Category -->
                                <div class="space-y-2">
                                    <Label for="category_id">
                                        Category
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Select v-model:model-value="form.category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="category in props.categories" :key="category.id"
                                                :value="category.id">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.category_id" class="text-sm text-destructive">
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>

                                <!-- Product Name -->
                                <div class="space-y-2">
                                    <Label for="name">
                                        Product Name
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="name" v-model="form.name" type="text" required />
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
                                    <Input id="slug" v-model="form.slug" type="text" />
                                    <p v-if="form.errors.slug" class="text-sm text-destructive">
                                        {{ form.errors.slug }}
                                    </p>
                                </div>

                                <!-- Base Price -->
                                <div class="space-y-2">
                                    <Label for="base_price">
                                        Base Price ($)
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input id="base_price" v-model.number="form.base_price" type="number" step="0.01"
                                        min="0" required />
                                    <p v-if="form.errors.base_price" class="text-sm text-destructive">
                                        {{ form.errors.base_price }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description" rows="4" />
                                <p v-if="form.errors.description" class="text-sm text-destructive">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Features -->
                            <div class="space-y-2">
                                <Label>Product Features</Label>
                                <div class="flex gap-2">
                                    <Input v-model="newFeature" placeholder="Add a feature" @keyup.enter="addFeature" />
                                    <Button type="button" @click="addFeature">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div v-if="form.features.length > 0" class="mt-2 space-y-2">
                                    <div v-for="(feature, index) in form.features" :key="index"
                                        class="flex items-center justify-between rounded-md border p-2">
                                        <span>{{ feature }}</span>
                                        <Button type="button" variant="ghost" size="sm" @click="removeFeature(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Toggles -->
                            <div class="flex gap-6">
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_featured" v-model="form.is_featured" />
                                    <Label for="is_featured">Featured</Label>
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="space-y-4 border-t pt-6">
                            <h3 class="text-lg font-semibold">Product Images</h3>

                            <!-- Featured Image -->
                            <div class="space-y-2">
                                <Label for="featured_image">Featured Image</Label>
                                <div v-if="featuredImagePreview" class="mb-2">
                                    <img :src="featuredImagePreview" alt="Preview"
                                        class="h-40 w-40 rounded-md border object-cover" />
                                </div>
                                <Input id="featured_image" type="file" accept="image/*"
                                    @change="handleFeaturedImageChange" />
                                <p class="text-xs text-muted-foreground">Upload a new image to replace the current one
                                </p>
                            </div>

                            <!-- Existing Additional Images -->
                            <div v-if="existingImages.length > 0" class="space-y-2">
                                <Label>Current Additional Images</Label>
                                <div class="grid grid-cols-4 gap-4">
                                    <div v-for="(image, index) in existingImages" :key="image.id" class="relative">
                                        <img :src="image.url" :alt="image.name"
                                            class="h-24 w-24 rounded-md border object-cover" />
                                        <Button type="button" variant="destructive" size="sm"
                                            class="absolute -right-2 -top-2"
                                            @click="removeExistingImage(image.id, index)">
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- New Additional Images -->
                            <div class="space-y-2">
                                <Label for="images">Add More Images</Label>
                                <Input id="images" type="file" accept="image/*" multiple @change="handleImagesChange" />
                                <div v-if="imagePreviews.length > 0" class="mt-2 grid grid-cols-4 gap-4">
                                    <div v-for="(preview, index) in imagePreviews" :key="index" class="relative">
                                        <img :src="preview" alt="New image"
                                            class="h-24 w-24 rounded-md border object-cover" />
                                        <Button type="button" variant="destructive" size="sm"
                                            class="absolute -right-2 -top-2" @click="removeNewImage(index)">
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Filters (Based on Category) -->
                        <div v-if="categoryDetails" class="space-y-4 border-t pt-6">
                            <h3 class="text-lg font-semibold">Product Filters</h3>

                            <!-- Brand Filter -->
                            <div v-if="hasFilter('brand')" class="space-y-2">
                                <Label for="brand_id">
                                    Brand
                                    <span v-if="isFilterRequired('brand')" class="text-destructive">*</span>
                                </Label>
                                <Select v-model:model-value="form.brand_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a brand" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            <div class="flex items-center space-x-2">
                                                <img v-if="brand.logo_url" :src="brand.logo_url" :alt="brand.name"
                                                    class="h-6 w-6 rounded object-contain" />
                                                <div v-else
                                                    class="h-6 w-6 rounded bg-muted flex items-center justify-center">
                                                    <span class="text-xs font-bold">{{ brand.name.charAt(0) }}</span>
                                                </div>
                                                <span>{{ brand.name }}</span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.brand_id" class="text-sm text-destructive">
                                    {{ form.errors.brand_id }}
                                </p>
                            </div>

                            <!-- League Filter -->
                            <div v-if="hasFilter('league')" class="space-y-2">
                                <Label for="league_id">
                                    League
                                    <span v-if="isFilterRequired('league')" class="text-destructive">*</span>
                                </Label>
                                <Select v-model:model-value="form.league_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a league" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="league in leagues" :key="league.id" :value="league.id">
                                            <div class="flex items-center space-x-2">
                                                <img v-if="league.logo_url" :src="league.logo_url" :alt="league.name"
                                                    class="h-6 w-6 rounded object-contain" />
                                                <div v-else
                                                    class="h-6 w-6 rounded bg-muted flex items-center justify-center">
                                                    <span class="text-xs font-bold">{{ league.name.charAt(0) }}</span>
                                                </div>
                                                <span>{{ league.name }}</span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.league_id" class="text-sm text-destructive">
                                    {{ form.errors.league_id }}
                                </p>
                            </div>

                            <!-- Team Filter (Cascading) -->
                            <div v-if="hasFilter('team')" class="space-y-2">
                                <Label for="team_id">
                                    Team
                                    <span v-if="isFilterRequired('team')" class="text-destructive">*</span>
                                </Label>
                                <Select v-model:model-value="form.team_id" :disabled="!form.league_id">
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="form.league_id ? 'Select a team' : 'Select a league first'" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="team in availableTeams" :key="team.id" :value="team.id">
                                            <div class="flex items-center space-x-2">
                                                <img v-if="team.logo_url" :src="team.logo_url" :alt="team.name"
                                                    class="h-6 w-6 rounded object-contain" />
                                                <div v-else
                                                    class="h-6 w-6 rounded bg-muted flex items-center justify-center">
                                                    <span class="text-xs font-bold">{{ team.name.charAt(0) }}</span>
                                                </div>
                                                <span>{{ team.name }}</span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.team_id" class="text-sm text-destructive">
                                    {{ form.errors.team_id }}
                                </p>
                            </div>

                            <!-- Surface Type Filter -->
                            <div v-if="hasFilter('surface_type')" class="space-y-2">
                                <Label for="surface_type_id">
                                    Surface Type
                                    <span v-if="isFilterRequired('surface_type')" class="text-destructive">*</span>
                                </Label>
                                <Select v-model:model-value="form.surface_type_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a surface type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="surfaceType in surfaceTypes" :key="surfaceType.id"
                                            :value="surfaceType.id">
                                            {{ surfaceType.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.surface_type_id" class="text-sm text-destructive">
                                    {{ form.errors.surface_type_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Product Variants -->
                        <div v-if="form.variants.length > 0" class="space-y-4 border-t pt-6">
                            <h3 class="text-lg font-semibold">Product Variants (Sizes)</h3>
                            <p v-if="showValidationErrors && form.errors.variants" class="text-sm text-destructive">
                                {{ form.errors.variants }}
                            </p>
                            <div class="space-y-3">
                                <div v-for="(variant, index) in form.variants" :key="index"
                                    class="grid grid-cols-6 gap-4 rounded-md border p-4">
                                    <div class="space-y-2">
                                        <Label>Size</Label>
                                        <Input :model-value="getSizeLabel(variant.size)" disabled />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>SKU</Label>
                                        <Input v-model="variant.sku" placeholder="Optional" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Price Adj. ($)</Label>
                                        <Input v-model.number="variant.price_adjustment" type="number" step="0.01" />
                                        <p class="text-xs text-muted-foreground">
                                            Final: ${{ calculateFinalPrice(form.base_price,
                                            variant.price_adjustment).toFixed(2) }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Stock</Label>
                                        <Input v-model.number="variant.stock_quantity" type="number" min="0" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Low Stock Alert</Label>
                                        <Input v-model.number="variant.low_stock_threshold" type="number" min="0" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Active</Label>
                                        <Switch v-model="variant.is_active" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-4 border-t pt-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="ProductController.index.url()">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Product' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
