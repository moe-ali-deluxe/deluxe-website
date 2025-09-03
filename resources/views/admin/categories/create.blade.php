@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4">Create Category</h1>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Name --}}
        <div>
            <label class="block mb-1 font-semibold" for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="w-full border border-gray-300 rounded px-3 py-2"
                   required>
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="block mb-1 font-semibold" for="slug">Slug (optional)</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                   class="w-full border border-gray-300 rounded px-3 py-2">
            <small class="text-gray-600">If left empty, the slug will be auto-generated.</small>
            @error('slug') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Parent --}}
        <div>
            <label class="block mb-1 font-semibold" for="parent_id">Parent Category (optional)</label>
            <select id="parent_id" name="parent_id" class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="">-- None --</option>

                @php
                    // Expecting $categories to be a tree of root categories with ->children
                    $cats = $categories ?? collect();
                    function renderCategoryOptions($categories, $prefix = '') {
                        foreach ($categories as $category) {
                            echo '<option value="' . e($category->id) . '" ' . (old('parent_id') == $category->id ? 'selected' : '') . '>';
                            echo e($prefix . $category->name);
                            echo '</option>';
                            if ($category->children->count() > 0) {
                                renderCategoryOptions($category->children, $prefix . '-- ');
                            }
                        }
                    }
                @endphp

                @php renderCategoryOptions($cats); @endphp
            </select>
            @error('parent_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Image --}}
        <div>
            <label class="block mb-1 font-semibold" for="image">Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*"
                   class="w-full border border-gray-300 rounded px-3 py-2">
            <small class="text-gray-600">Recommended: square PNG/JPG/WebP, ≤ 4MB.</small>
            @error('image') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            {{-- Live preview --}}
            <div id="imagePreviewWrapper" class="mt-3 hidden">
                <div class="text-sm text-gray-600 mb-2">Preview:</div>
                <img id="imagePreview" src="#" alt="Preview"
                     class="h-24 w-24 object-contain rounded border bg-white p-1">
            </div>
        </div>

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Create Category
        </button>
    </form>

</div>

{{-- Slug auto-generate + image preview --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameEl = document.getElementById('name');
    const slugEl = document.getElementById('slug');
    const imgEl  = document.getElementById('image');
    const prevWrap = document.getElementById('imagePreviewWrapper');
    const prevImg  = document.getElementById('imagePreview');

    // Auto-generate slug if slug is empty (doesn't overwrite manual edits)
    nameEl?.addEventListener('input', () => {
        if (!slugEl || slugEl.value.trim().length) return;
        slugEl.value = nameEl.value
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });

    // Live image preview
    imgEl?.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) {
            prevWrap.classList.add('hidden');
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            prevImg.src = ev.target.result;
            prevWrap.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
