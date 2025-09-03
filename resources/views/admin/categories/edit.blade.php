@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4">Edit Category: {{ $category->name }}</h1>

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

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div>
            <label class="block mb-1 font-semibold" for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                   class="w-full border border-gray-300 rounded px-3 py-2" required>
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="block mb-1 font-semibold" for="slug">Slug (optional)</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                   class="w-full border border-gray-300 rounded px-3 py-2">
            <small class="text-gray-600">If left empty, slug will be auto-generated.</small>
            @error('slug') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Parent --}}
        <div>
            <label class="block mb-1 font-semibold" for="parent_id">Parent Category (optional)</label>
            <select id="parent_id" name="parent_id" class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="">-- None --</option>

                @php
                // Recursive renderer that excludes the current category and all its descendants
                function renderCategoryOptionsEdit($categories, $currentCategory, $prefix = '') {
                    foreach ($categories as $cat) {
                        // Skip current category and its descendants
                        if ($cat->id === $currentCategory->id) continue;

                        // Exclude descendants (walk up the tree from candidate)
                        $exclude = false;
                        $p = $cat->parent ?? null;
                        while ($p) {
                            if ($p->id === $currentCategory->id) { $exclude = true; break; }
                            $p = $p->parent ?? null;
                        }
                        if ($exclude) continue;

                        $selected = old('parent_id', $currentCategory->parent_id) == $cat->id ? 'selected' : '';
                        echo '<option value="'.e($cat->id).'" '.$selected.'>'.e($prefix.$cat->name).'</option>';

                        if ($cat->children->count()) {
                            renderCategoryOptionsEdit($cat->children, $currentCategory, $prefix.'-- ');
                        }
                    }
                }
                @endphp

                @php renderCategoryOptionsEdit($categories, $category); @endphp
            </select>
            @error('parent_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Current Image --}}
        <div class="space-y-2">
            <span class="block mb-1 font-semibold">Current Image</span>
            @if(!empty($category->image))
                <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="h-24 w-24 object-contain rounded border bg-white p-1">
                <label class="inline-flex items-center gap-2 mt-2">
                    <input type="checkbox" name="remove_image" value="1">
                    <span>Remove image</span>
                </label>
            @else
                <p class="text-gray-500 text-sm">None</p>
            @endif
        </div>

        {{-- Upload / Replace Image --}}
        <div>
            <label class="block mb-1 font-semibold" for="image">Replace / Upload New Image</label>
            <input type="file" id="image" name="image" accept="image/*"
                   class="w-full border border-gray-300 rounded px-3 py-2">
            <small class="text-gray-600">Recommended: square PNG/JPG/WebP, ≤ 4MB.</small>
            @error('image') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            {{-- Live preview for newly selected file --}}
            <div id="imagePreviewWrapper" class="mt-3 hidden">
                <div class="text-sm text-gray-600 mb-2">New image preview:</div>
                <img id="imagePreview" src="#" alt="Preview" class="h-24 w-24 object-contain rounded border bg-white p-1">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Update Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                Cancel
            </a>
        </div>
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

    // Auto-generate slug if empty (doesn't overwrite manual edits)
    nameEl?.addEventListener('input', () => {
        if (!slugEl || slugEl.value.trim().length) return;
        slugEl.value = nameEl.value
            .toString().toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });

    // Live preview
    imgEl?.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) { prevWrap.classList.add('hidden'); return; }
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
