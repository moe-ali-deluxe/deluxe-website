@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Edit Product</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form id="product-edit-form" action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block font-medium">Name</label>
                <input type="text" name="name" id="name" class="w-full border p-2 rounded" value="{{ old('name', $product->name) }}" required>
            </div>

            <div>
                <label for="slug" class="block font-medium">Slug</label>
                <input type="text" name="slug" id="slug" class="w-full border p-2 rounded" value="{{ old('slug', $product->slug) }}">
            </div>

            <div>
                <label for="sku" class="block font-medium">SKU</label>
                <input type="text" name="sku" id="sku" class="w-full border p-2 rounded" value="{{ old('sku', $product->sku) }}">
            </div>

            <div>
                <label for="barcode" class="block font-medium">Barcode</label>
                <input type="text" name="barcode" id="barcode" class="w-full border p-2 rounded" value="{{ old('barcode', $product->barcode) }}">
            </div>

            <div>
                <label for="price" class="block font-medium">Price ($)</label>
                <input type="number" name="price" id="price" step="0.01" class="w-full border p-2 rounded" value="{{ old('price', $product->price) }}" required>
            </div>

            <div>
                <label for="discount_price" class="block font-medium">Discount Price ($)</label>
                <input type="number" name="discount_price" id="discount_price" step="0.01" class="w-full border p-2 rounded" value="{{ old('discount_price', $product->discount_price) }}">
            </div>

            <div>
                <label for="stock" class="block font-medium">Stock</label>
                <input type="number" name="stock" id="stock" class="w-full border p-2 rounded" value="{{ old('stock', $product->stock) }}" min="0" required>
            </div>

            <div>
                <label for="brand_id" class="block font-medium">Brand</label>
                <select name="brand_id" id="brand_id" class="w-full border p-2 rounded">
                    <option value="">-- Select Brand --</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rating" class="block font-medium">Rating</label>
                <input type="number" step="0.1" min="0" max="5" name="rating" id="rating" class="w-full border p-2 rounded" value="{{ old('rating', $product->rating) }}">
            </div>

            <div>
                <label for="vendor_id" class="block font-medium">Vendor</label>
                <select name="vendor_id" id="vendor_id" class="w-full border p-2 rounded">
                    <option value="">-- Select Vendor --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="category_id" class="block font-medium">Category</label>
                <select name="category_id" id="category_id" class="w-full border p-2 rounded" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $parent)
                        <option value="{{ $parent->id }}" {{ old('category_id', $product->category_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                        @foreach($parent->children as $child)
                            @foreach($child->children as $subChild)
                                <option value="{{ $subChild->id }}" {{ old('category_id', $product->category_id) == $subChild->id ? 'selected' : '' }}>
                                    -- {{ $subChild->name }}
                                </option>
                            @endforeach
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="flex items-center mt-6 space-x-6">
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    <span class="ml-2">Active</span>
                </label>

                <input type="hidden" name="featured" value="0">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                    <span class="ml-2">Featured</span>
                </label>

                <input type="hidden" name="is_new" value="0">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
                    <span class="ml-2">New</span>
                </label>
            </div>
        </div>

        <div class="mt-4">
            <label for="description" class="block font-medium">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full border p-2 rounded">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mt-4">
            <label for="meta_title" class="block font-medium">Meta Title</label>
            <input type="text" name="meta_title" id="meta_title" class="w-full border p-2 rounded" value="{{ old('meta_title', $product->meta_title) }}">
        </div>

        <div class="mt-4">
            <label for="meta_description" class="block font-medium">Meta Description</label>
            <textarea name="meta_description" id="meta_description" rows="3" class="w-full border p-2 rounded">{{ old('meta_description', $product->meta_description) }}</textarea>
        </div>

        {{-- Existing Images Manager --}}
        @if ($product->images->isNotEmpty())
            <div class="mt-8">
                <h3 class="font-semibold mb-3">Existing Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($product->images as $image)
                        <div class="border rounded p-3 bg-gray-50 relative image-wrapper" data-image-id="{{ $image->id }}">
                            <div class="flex gap-3">
                                <img
                                    src="{{ $image->url ?? asset('storage/' . $image->image) }}"
                                    alt="{{ $image->alt ?? $product->name }}"
                                    class="w-24 h-24 object-cover rounded border"
                                />
                                <div class="flex-1 space-y-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Alt text</label>
                                        <input
                                            type="text"
                                            name="existing_images[{{ $image->id }}][alt]"
                                            class="w-full border p-2 rounded"
                                            value="{{ old("existing_images.{$image->id}.alt", $image->alt) }}"
                                            placeholder="Describe this image"
                                        >
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="inline-flex items-center gap-2">
                                            <input
                                                type="radio"
                                                name="primary_image_id"
                                                value="{{ $image->id }}"
                                                {{ old('primary_image_id', $image->is_primary ? $image->id : null) == $image->id ? 'checked' : '' }}
                                            >
                                            <span class="text-sm">Primary</span>
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <span class="text-sm">Sort</span>
                                            <input
                                                type="number"
                                                name="existing_images[{{ $image->id }}][sort_order]"
                                                value="{{ old("existing_images.{$image->id}.sort_order", $image->sort_order ?? 0) }}"
                                                class="w-20 border p-2 rounded"
                                                min="0"
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Delete --}}
                            <button
                                type="button"
                                class="delete-image-btn absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded"
                                data-id="{{ $image->id }}"
                                title="Delete image"
                            >✕</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Add New Images --}}
        <div class="mt-8">
            <label for="images" class="block font-semibold">Add New Images</label>
            <input type="file" name="images[]" id="images" multiple class="w-full border p-2 rounded" accept="image/*">
            @error('images.*')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror

            <p class="text-xs text-gray-600 mt-1">
                Set per-image <strong>Alt</strong>, choose a <strong>Primary</strong> (optional), and a <strong>Sort</strong> for each uploaded file.
            </p>

            {{-- New uploads preview + per-file fields (built by JS) --}}
            <div id="image-preview-list" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4"></div>

            {{-- Hidden field populated on submit if a "new upload" primary is chosen --}}
            <input type="hidden" name="primary_index" id="primary_index">
        </div>

        <div class="text-right mt-8">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Update Product
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ----- Delete existing image -----
    const baseDeleteUrl = @json(route('admin.products.images.destroy', ['image' => 'IMAGE_ID_PLACEHOLDER']));
    document.querySelectorAll('.delete-image-btn').forEach(button => {
        button.addEventListener('click', function () {
            const imageId = this.dataset.id;
            const wrapper = this.closest('.image-wrapper');
            const url = baseDeleteUrl.replace('IMAGE_ID_PLACEHOLDER', imageId);

            if (confirm('Are you sure you want to delete this image?')) {
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (res.ok) {
                        wrapper.remove();
                    } else {
                        alert('Failed to delete image.');
                    }
                })
                .catch(() => alert('Error occurred while deleting image.'));
            }
        });
    });

    // ----- New uploads preview with alt/sort & "new primary" radio -----
    const fileInput = document.getElementById('images');
    const list = document.getElementById('image-preview-list');
    const nameInput = document.getElementById('name');
    const primaryIndexHidden = document.getElementById('primary_index');

    function card(file, i) {
        const url = URL.createObjectURL(file);
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded p-3 flex gap-3 items-start bg-gray-50';

        wrapper.innerHTML = `
            <img src="${url}" alt="" class="w-20 h-20 object-cover rounded border">
            <div class="flex-1 space-y-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Alt text</label>
                    <input type="text" name="images_alt[]" class="w-full border p-2 rounded"
                           placeholder="e.g., ${(nameInput?.value || 'Product') + ' - image ' + (i+1)}">
                </div>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="new_primary_index" value="${i}">
                        <span class="text-sm">Primary</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <span class="text-sm">Sort</span>
                        <input type="number" name="images_sort[]" value="${i}" class="w-20 border p-2 rounded" min="0">
                    </label>
                </div>
                <p class="text-xs text-gray-500 break-all">${file.name} • ${(file.size/1024).toFixed(0)} KB</p>
            </div>
        `;
        return wrapper;
    }

    fileInput?.addEventListener('change', () => {
        list.innerHTML = '';
        const files = Array.from(fileInput.files || []);
        files.forEach((file, i) => list.appendChild(card(file, i)));
    });

    // On submit: if a "new upload" primary is selected, copy it into the hidden primary_index.
    document.getElementById('product-edit-form').addEventListener('submit', () => {
        const chosen = document.querySelector('input[name="new_primary_index"]:checked');
        primaryIndexHidden.value = chosen ? chosen.value : '';
        // Note: if you selected a new upload as primary, your controller (as shared) will
        // clear old primaries and set the chosen new one as primary.
        // If none chosen, the "primary_image_id" for existing images can be used (see controller note below).
    });
});
</script>
@endpush
