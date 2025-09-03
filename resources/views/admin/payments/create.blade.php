@extends('layouts.app')

@section('title', 'Create Payment')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded-lg mt-6">
    <h1 class="text-3xl font-bold mb-6">Create Payment</h1>

    <form action="{{ route('admin.payments.store') }}" method="POST">
        @csrf

        {{-- Order --}}
        <div class="mb-4">
            <label for="order_id" class="block font-medium mb-1">Order</label>
            <select name="order_id" id="order_id" class="w-full border rounded p-2">
                @foreach($orders as $orderItem)
                    <option value="{{ $orderItem->id }}" {{ old('order_id') == $orderItem->id ? 'selected' : '' }}>
                        Order #{{ $orderItem->id }} - Total: {{ number_format($orderItem->total_amount,2) }}
                    </option>
                @endforeach
            </select>
            @error('order_id') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Remaining Balance --}}
        @php
            $ordersJS = $orders->map(function($o){
                $alreadyPaid = $o->payments->sum('amount');
                return [
                    'id' => $o->id,
                    'remaining' => $o->total_amount - $alreadyPaid
                ];
            });
            $firstOrder = $orders->first();
            $remaining = $firstOrder ? $firstOrder->total_amount - $firstOrder->payments->sum('amount') : 0;
        @endphp
        <div class="mb-4">
            <span class="text-gray-600">Remaining Balance: <strong id="remaining">{{ number_format($remaining,2) }}</strong></span>
        </div>

        {{-- Amount --}}
        <div class="mb-4">
            <label for="amount" class="block font-medium mb-1">Amount</label>
            <input type="number" step="0.01" min="0" max="{{ $remaining }}" 
                   name="amount" id="amount" value="{{ old('amount') }}"
                   class="w-full border rounded p-2">
            @error('amount') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Payment Method --}}
        <div class="mb-4">
            <label for="payment_method" class="block font-medium mb-1">Payment Method</label>
            <select name="payment_method" id="payment_method" class="w-full border rounded p-2">
                @foreach($methods as $method)
                    <option value="{{ $method }}" {{ old('payment_method') == $method ? 'selected' : '' }}>
                        {{ ucfirst($method) }}
                    </option>
                @endforeach
            </select>
            @error('payment_method') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Status --}}
        <div class="mb-4">
            <label for="status" class="block font-medium mb-1">Status</label>
            <select name="status" id="status" class="w-full border rounded p-2">
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ',$status)) }}
                    </option>
                @endforeach
            </select>
            @error('status') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Transaction ID --}}
        <div class="mb-4">
            <label for="transaction_id" class="block font-medium mb-1">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" 
                   value="{{ old('transaction_id') }}" class="w-full border rounded p-2">
            @error('transaction_id') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Paid At --}}
        <div class="mb-4">
            <label for="paid_at" class="block font-medium mb-1">Paid At</label>
            <input type="datetime-local" name="paid_at" id="paid_at" 
                   value="{{ old('paid_at') }}" class="w-full border rounded p-2">
            @error('paid_at') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Create Payment
        </button>
    </form>
</div>

<script>
    const orders = @json($ordersJS);
    const orderSelect = document.getElementById('order_id');
    const remainingSpan = document.getElementById('remaining');
    const amountInput = document.getElementById('amount');

    orderSelect.addEventListener('change', function(){
        const selected = orders.find(o => o.id == this.value);
        remainingSpan.textContent = selected.remaining.toFixed(2);
        amountInput.max = selected.remaining;
    });
</script>
@endsection
