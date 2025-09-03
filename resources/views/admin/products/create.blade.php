@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Add New Product</h2>

    @if(session('success'))
        <div id="success-alert" class="bg-green-100 text-green-800 p-4 mb-4 rounded shadow">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if(alert) {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 4000);
        </script>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Name --}}
        <div class="mb-4">
            <label for="name" class="block font-medium">Name</label>
            <input type="text" name="name" id="name" class="w-full border p-2 rounded" value="{{ old('name') }}" required>
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label for="description" class="block font-medium">Description</label>
            <textarea name="description" id="description" class="w-full border p-2 rounded" rows="4" required>{{ old('description') }}</textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Price --}}
        <div class="mb-4">
            <label for="price" class="block font-medium">Price ($)</label>
            <input type="number" name="price" step="0.01" id="price" class="w-full border p-2 rounded" value="{{ old('price') }}" required>
            @error('price') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Vendor --}}
        <div class="mb-4">
            <label for="vendor_id" class="block font-medium">Vendor</label>
            <select name="vendor_id" id="vendor_id" class="w-full border p-2 rounded" required>
                <option value="">-- Select Vendor --</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            @error('vendor_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Barcode --}}
        <div class="mb-4">
            <label for="barcode" class="block font-medium">Barcode</label>
            <input type="text" name="barcode" id="barcode" class="w-full border p-2 rounded" value="{{ old('barcode') }}">
            @error('barcode') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Rating --}}
        <div class="mb-4">
            <label for="rating" class="block font-medium">Rating (0 - 5)</label>
            <input type="number" name="rating" id="rating" class="w-full border p-2 rounded" min="0" max="5" step="0.1" value="{{ old('rating') }}">
            @error('rating') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Is New --}}
        <div class="mb-4">
            <input type="hidden" name="is_new" value="0">
            <label for="is_new" class="inline-flex items-center">
                <input type="checkbox" name="is_new" id="is_new" class="mr-2" value="1" {{ old('is_new') ? 'checked' : '' }}>
                New
            </label>
        </div>

        {{-- Product Images --}}
        <div class="mb-6">
            <label for="images" class="block font-medium">Product Images</label>
            <input type="file" name="images[]" id="images" class="w-full border p-2 rounded" multiple accept="image/*">
            <p class="text-xs text-gray-600 mt-1">You can upload multiple images. Set an <strong>Alt</strong>, choose the <strong>Primary</strong> one, and assign <strong>Sort order</strong>.</p>

            @error('images') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            @if ($errors->has('images.*'))
                @foreach ($errors->get('images.*') as $messages)
                    @foreach ($messages as $message)
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @endforeach
                @endforeach
            @endif

            {{-- Previews + per-image fields (built by JS on file selection) --}}
            <div id="image-preview-list" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4"></div>

            {{-- This single radio group captures which selected file index is primary --}}
            {{-- Will be created by JS as <input type="radio" name="primary_index" value="i"> in each card --}}
            @error('primary_index') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Category --}}
        <div class="mb-4">
            <label for="category_id" class="block font-medium">Category</label>
            <select name="category_id" id="category_id" class="w-full border p-2 rounded" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $parent)
                    <option value="{{ $parent->id }}" {{ old('category_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                    @foreach($parent->children as $child)
                        @foreach($child->children as $subChild)
                            <option value="{{ $subChild->id }}" {{ old('category_id') == $subChild->id ? 'selected' : '' }}>
                                -- {{ $subChild->name }}
                            </option>
                        @endforeach
                    @endforeach
                @endforeach
            </select>
            @error('category_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Brand --}}
        <div class="mb-4">
            <label for="brand_id" class="block font-medium">Brand</label>
            <select name="brand_id" id="brand_id" class="w-full border p-2 rounded">
                <option value="">-- Select Brand --</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
            @error('brand_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Discount Price --}}
        <div class="mb-4">
            <label for="discount_price" class="block font-medium">Discount Price ($)</label>
            <input type="number" step="0.01" name="discount_price" id="discount_price" class="w-full border p-2 rounded" value="{{ old('discount_price') }}">
            @error('discount_price') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Active --}}
        <div class="mb-4">
            <input type="hidden" name="is_active" value="0">
            <label for="is_active" class="inline-flex items-center">
                <input type="checkbox" name="is_active" id="is_active" class="mr-2" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                Active
            </label>
        </div>

        {{-- Featured --}}
        <div class="mb-4">
            <input type="hidden" name="featured" value="0">
            <label for="featured" class="inline-flex items-center">
                <input type="checkbox" name="featured" id="featured" class="mr-2" value="1" {{ old('featured') ? 'checked' : '' }}>
                Featured
            </label>
        </div>

        {{-- Stock --}}
        <div class="mb-6">
            <label for="stock" class="block font-medium">Stock</label>
            <input type="number" name="stock" id="stock" class="w-full border p-2 rounded" value="{{ old('stock') }}" min="0" required>
            @error('stock') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="text-right">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Save Product
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const fileInput = document.getElementById('images');
    const list = document.getElementById('image-preview-list');
    const nameInput = document.getElementById('name');

    function buildCard(file, i) {
        const url = URL.createObjectURL(file);
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded p-3 flex gap-3 items-start bg-gray-50';

        wrapper.innerHTML = `
            <img src="${url}" alt="" class="w-20 h-20 object-cover rounded border">
            <div class="flex-1 space-y-2">
                <div>
                    <label class="block text-sm font-medium mb-1">Alt text</label>
                    <input type="text" name="images_alt[]" class="w-full border p-2 rounded"
                           placeholder="e.g., ${nameInput.value ? nameInput.value + ' - image ' + (i+1) : 'Describe this image'}">
                </div>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="primary_index" value="${i}" ${i === 0 ? 'checked' : ''}>
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
        files.forEach((file, i) => {
            const card = buildCard(file, i);
            list.appendChild(card);
        });
    });
})();
</script>
@endpush
