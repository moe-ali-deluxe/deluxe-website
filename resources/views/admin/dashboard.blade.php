@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
  // Safe fallbacks so the view never 500s
  $totalProducts        = $totalProducts        ?? 0;
  $activeProducts       = $activeProducts       ?? 0;
  $outOfStockCount      = $outOfStockCount      ?? 0;
  $totalOrders          = $totalOrders          ?? 0;
  $ordersLast30         = $ordersLast30         ?? 0;
  $totalRevenue         = $totalRevenue         ?? 0;
  $revenueLast30        = $revenueLast30        ?? 0;
  $categoriesCount      = $categoriesCount      ?? 0;
  $brandsCount          = $brandsCount          ?? 0;
  $vendorsCount         = $vendorsCount         ?? 0;

  $ordersByStatus       = collect($ordersByStatus ?? []);
  $lowStockProducts     = collect($lowStockProducts ?? []);
  $topProducts          = collect($topProducts ?? []);
  $recentOrders         = collect($recentOrders ?? []);
  $recentPayments       = collect($recentPayments ?? []);
@endphp

<div class="container mx-auto p-4 space-y-8">

  <div class="flex items-center justify-between">
    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
  </div>

  {{-- Quick Actions --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
    <a href="{{ route('admin.categories.create') }}"
       class="px-5 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-center">
       + Add New Category
    </a>
    <a href="{{ route('admin.categories.index') }}"
       class="px-5 py-3 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition text-center">
       ✏️ Edit Categories
    </a>
    <a href="{{ route('admin.products.create') }}"
       class="px-5 py-3 bg-green-600 text-white rounded hover:bg-green-700 transition text-center">
       + Add New Product
    </a>
    <a href="{{ route('admin.products.index') }}"
       class="px-5 py-3 bg-teal-600 text-white rounded hover:bg-teal-700 transition text-center">
       ✏️ Manage Products
    </a>
    <a href="{{ route('admin.vendors.create') }}"
       class="px-5 py-3 bg-purple-600 text-white rounded hover:bg-purple-700 transition text-center">
       + Add New Vendor
    </a>
    <a href="{{ route('admin.vendors.index') }}"
       class="px-5 py-3 bg-pink-600 text-white rounded hover:bg-pink-700 transition text-center">
       ✏️ Manage Vendors
    </a>
    <a href="{{ route('admin.brands.create') }}"
       class="px-5 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition text-center">
       + Add New Brand
    </a>
    <a href="{{ route('admin.brands.index') }}"
       class="px-5 py-3 bg-orange-500 text-white rounded hover:bg-orange-600 transition text-center">
       ✏️ Manage Brands
    </a>
    <a href="{{ route('admin.payments.create') }}"
       class="px-5 py-3 bg-gray-600 text-white rounded hover:bg-gray-700 transition text-center">
       + Add New Payment
    </a>
    <a href="{{ route('admin.payments.index') }}"
       class="px-5 py-3 bg-gray-500 text-white rounded hover:bg-gray-600 transition text-center">
       ✏️ Manage Payments
    </a>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4">
    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Products</div>
      <div class="text-2xl font-bold">{{ $totalProducts }}</div>
      <div class="text-xs text-gray-500 mt-1">Active: {{ $activeProducts }}</div>
      <div class="text-xs text-red-600 mt-1">Out of stock: {{ $outOfStockCount }}</div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Orders (30d)</div>
      <div class="text-2xl font-bold">{{ $ordersLast30 }}</div>
      <div class="text-xs text-gray-500 mt-1">Total: {{ $totalOrders }}</div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Revenue (total)</div>
      <div class="text-2xl font-bold">${{ number_format($totalRevenue, 2) }}</div>
      <div class="text-xs text-gray-500 mt-1">Last 30d: ${{ number_format($revenueLast30, 2) }}</div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Categories</div>
      <div class="text-2xl font-bold">{{ $categoriesCount }}</div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Brands</div>
      <div class="text-2xl font-bold">{{ $brandsCount }}</div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <div class="text-sm text-gray-500">Vendors</div>
      <div class="text-2xl font-bold">{{ $vendorsCount }}</div>
    </div>
  </div>

  {{-- Orders by Status --}}
  <div class="p-4 bg-white rounded-xl shadow border">
    <h2 class="text-xl font-bold mb-3">Orders by Status</h2>
    @if($ordersByStatus->isEmpty())
      <div class="text-gray-500 text-sm">No order data.</div>
    @else
      <div class="flex gap-2 flex-wrap">
        @foreach($ordersByStatus as $status => $count)
          <span class="px-3 py-1 rounded-full border text-sm bg-gray-50">
            {{ ucfirst($status ?? 'unknown') }}: <span class="font-semibold">{{ $count }}</span>
          </span>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Low Stock & Top Products --}}
  <div class="grid md:grid-cols-2 gap-6">
    <div class="p-4 bg-white rounded-xl shadow border">
      <h2 class="text-xl font-bold mb-3">Low stock (≤ 5)</h2>
      <div class="space-y-2">
        @forelse($lowStockProducts as $p)
          <div class="p-3 border rounded flex items-center justify-between">
            <div>
              <div class="font-semibold">{{ $p->name }}</div>
              <div class="text-sm text-gray-600">
                @if($p->brand) Brand: {{ $p->brand->name }} • @endif
                @if($p->category) Category: {{ $p->category->name }} • @endif
                Price: ${{ number_format($p->price, 2) }}
              </div>
            </div>
            <div class="text-red-600 font-semibold">Stock: {{ $p->stock }}</div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">No low-stock items 🎉</div>
        @endforelse
      </div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <h2 class="text-xl font-bold mb-3">Top products (by qty)</h2>
      <div class="space-y-2">
        @forelse($topProducts as $row)
          @php $prod = $row->product ?? null; @endphp
          <div class="p-3 border rounded">
            <div class="font-semibold">
              {{ $prod?->name ?? ('#'.$row->product_id) }}
            </div>
            <div class="text-sm text-gray-600">
              Sold: <span class="font-medium">{{ (int)($row->qty ?? 0) }}</span>
              • Sales: ${{ number_format((float)($row->sales ?? 0), 2) }}
              @if($prod?->brand) • Brand: {{ $prod->brand->name }} @endif
            </div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">No sales data yet.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Recent Orders / Payments --}}
  <div class="grid md:grid-cols-2 gap-6">
    <div class="p-4 bg-white rounded-xl shadow border">
      <h2 class="text-xl font-bold mb-3">Recent Orders</h2>
      <div class="space-y-2">
        @forelse($recentOrders as $o)
          <div class="p-3 border rounded">
            <div class="font-medium">#{{ $o->id }} • {{ ucfirst($o->status ?? 'pending') }}</div>
            <div class="text-sm text-gray-600">
              {{ optional($o->created_at)->format('Y-m-d H:i') }}
              @if($o->user) • {{ $o->user->name }} @endif
              @if(isset($o->total_amount)) • ${{ number_format($o->total_amount, 2) }} @endif
            </div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">No orders yet.</div>
        @endforelse
      </div>
    </div>

    <div class="p-4 bg-white rounded-xl shadow border">
      <h2 class="text-xl font-bold mb-3">Recent Payments</h2>
      <div class="space-y-2">
        @forelse($recentPayments as $pmt)
          <div class="p-3 border rounded">
            <div class="font-medium">
              ${{ number_format((float)($pmt->amount ?? 0), 2) }}
              • {{ ucfirst($pmt->status ?? 'pending') }}
              @if(!empty($pmt->method)) • {{ $pmt->method }} @endif
            </div>
            <div class="text-sm text-gray-600">
              {{ optional($pmt->created_at)->format('Y-m-d H:i') }}
              @if($pmt->order) • Order #{{ $pmt->order->id }} @endif
            </div>
          </div>
        @empty
          <div class="text-gray-500 text-sm">No payments yet.</div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
