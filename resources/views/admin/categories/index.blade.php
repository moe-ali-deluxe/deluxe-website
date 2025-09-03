@extends('layouts.app') {{-- or your admin layout --}}

@section('title', 'Categories')

@section('content')
<div class="container mx-auto p-4">

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Categories</h1>
    <a href="{{ route('admin.categories.create') }}"
       class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
       + Add New Category
    </a>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-300">
      {{ session('success') }}
    </div>
  @endif

  @php
    // Accept either variable name from the controller
    $tree = ($categoryTree ?? $categories ?? collect());

    // Helpers (guard against re-declare)
    if (!function_exists('admin_children')) {
        function admin_children($cat) {
            // Prefer whichever relation is actually eager-loaded
            if ($cat->relationLoaded('children')) return $cat->children;
            if ($cat->relationLoaded('childrenRecursive')) return $cat->childrenRecursive;
            // nothing eager-loaded: return empty to avoid N+1s
            return collect();
        }
    }

    if (!function_exists('admin_total_count')) {
        function admin_total_count($cat) {
            // If you added the accessor on the model, prefer that
            if (isset($cat->total_products_count)) {
                return (int) $cat->total_products_count;
            }
            // Fallback: sum direct + descendants using the loaded relation
            $sum = (int) ($cat->products_count ?? 0);
            $kids = admin_children($cat);
            if ($kids && $kids->count()) {
                foreach ($kids as $child) {
                    $sum += admin_total_count($child);
                }
            }
            return $sum;
        }
    }

    if (!function_exists('admin_render_category_rows')) {
        function admin_render_category_rows($nodes, $prefix = '— ') {
            foreach ($nodes as $cat) {
                $count = admin_total_count($cat);

                echo '<tr class="hover:bg-gray-50">';

                // Image
                echo '<td class="border px-4 py-2 align-middle">';
                if (!empty($cat->image)) {
                    echo '<img src="'.e(asset('storage/'.$cat->image)).'" alt="'.e($cat->name).'" class="h-10 w-10 object-contain rounded border bg-white p-1">';
                } else {
                    echo '<span class="text-gray-400 text-sm">—</span>';
                }
                echo '</td>';

                // Name (with indent)
                echo '<td class="border px-4 py-2 align-middle"><span class="text-gray-900 font-medium">'
                    . e($prefix.$cat->name) . '</span></td>';

                // Slug
                echo '<td class="border px-4 py-2 align-middle text-gray-600">'
                    . e($cat->slug) . '</td>';

                // Parent
                $parentName = $cat->parent->name ?? '-';
                echo '<td class="border px-4 py-2 align-middle text-gray-600">'
                    . e($parentName) . '</td>';

                // Products (total including descendants)
                echo '<td class="border px-4 py-2 align-middle text-gray-800 font-semibold">'
                    . (int) $count . '</td>';

                // Actions
                echo '<td class="border px-4 py-2 align-middle space-x-3">';
                echo '<a href="'.e(route('admin.categories.edit', $cat)).'" class="text-blue-600 hover:underline">Edit</a>';
                echo '<form action="'.e(route('admin.categories.destroy', $cat)).'" method="POST" class="inline-block" onsubmit="return confirm(\'Delete this category?\');">';
                echo csrf_field().method_field('DELETE');
                echo '<button type="submit" class="text-red-600 hover:underline">Delete</button>';
                echo '</form>';
                echo '</td>';

                echo '</tr>';

                // Children (use whichever relation is loaded)
                $children = admin_children($cat);
                if ($children && $children->count()) {
                    admin_render_category_rows($children, $prefix.'— ');
                }
            }
        }
    }
  @endphp

  @if($tree->count())
    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-200 rounded shadow-sm">
        <thead>
          <tr class="bg-gray-50 text-left text-gray-700">
            <th class="border px-4 py-3 w-20">Image</th>
            <th class="border px-4 py-3">Name</th>
            <th class="border px-4 py-3">Slug</th>
            <th class="border px-4 py-3">Parent</th>
            <th class="border px-4 py-3 w-28">Products</th>
            <th class="border px-4 py-3 w-40">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php admin_render_category_rows($tree, ''); @endphp
        </tbody>
      </table>
    </div>
  @else
    <p>No categories found.</p>
  @endif

</div>
@endsection
