@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded-lg mt-6">
    <h1 class="text-3xl font-bold mb-6">Edit Payment #{{ $payment->id }}</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Order --}}
        <div class="mb-4">
            <label for="order_id" class="block font-medium mb-1">Order</label>
            <select name="order_id" id="order_id" class="w-full border rounded p-2">
                @foreach($orders as $order)
                    <option value="{{ $order->id }}" 
                        {{ $order->id == old('order_id', $payment->order_id) ? 'selected' : '' }}>
                        Order #{{ $order->id }} - Total: {{ number_format($order->total_amount,2) }}
                    </option>
                @endforeach
            </select>
            @error('order_id') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Remaining Balance --}}
        <div class="mb-4">
            <span class="text-gray-600">
                Remaining Balance: <strong id="remaining">{{ number_format($remaining, 2) }}</strong>
            </span>
        </div>

        {{-- Amount --}}
        <div class="mb-4">
            <label for="amount" class="block font-medium mb-1">Amount</label>
            <input type="number" step="0.01" min="0" 
                   max="{{ $remaining }}" 
                   name="amount" id="amount" 
                   value="{{ old('amount', $payment->amount) }}" 
                   class="w-full border rounded p-2">
            @error('amount') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Payment Method --}}
        <div class="mb-4">
            <label for="payment_method" class="block font-medium mb-1">Payment Method</label>
            <select name="payment_method" id="payment_method" class="w-full border rounded p-2">
                @foreach($methods as $method)
                    <option value="{{ $method }}" {{ $method == old('payment_method', $payment->payment_method) ? 'selected' : '' }}>
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
                    <option value="{{ $status }}" {{ $status == old('status', $payment->status) ? 'selected' : '' }}>
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
                   value="{{ old('transaction_id', $payment->transaction_id) }}" 
                   class="w-full border rounded p-2">
            @error('transaction_id') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Paid At --}}
        <div class="mb-4">
            <label for="paid_at" class="block font-medium mb-1">Paid At</label>
            <input type="datetime-local" name="paid_at" id="paid_at" 
                   value="{{ old('paid_at', $payment->paid_at ? $payment->paid_at->format('Y-m-d\TH:i') : '') }}" 
                   class="w-full border rounded p-2">
            @error('paid_at') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            Update Payment
        </button>
    </form>
</div>

<script>
    const orders = @json($ordersJS);
    const orderSelect = document.getElementById('order_id');
    const remainingSpan = document.getElementById('remaining');
    const amountInput = document.getElementById('amount');

    orderSelect.addEventListener('change', function(){
        const selected = orders[this.value];
        remainingSpan.textContent = parseFloat(selected).toFixed(2);
        amountInput.max = selected;
    });
</script>
@endsection
