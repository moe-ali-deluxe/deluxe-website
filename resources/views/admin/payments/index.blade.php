@extends('layouts.app')

@section('title', 'Manage Payments')

@section('content')
<div class="container mx-auto p-4">

    <h1 class="text-2xl font-bold mb-4">Payments</h1>

    <a href="{{ route('admin.payments.create') }}" 
       class="inline-block mb-4 px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
       + Add New Payment
    </a>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <table class="table-auto w-full border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1">#</th>
                <th class="border px-2 py-1">Order</th>
                <th class="border px-2 py-1">User</th>
                <th class="border px-2 py-1">Amount</th>
                <th class="border px-2 py-1">Method</th>
                <th class="border px-2 py-1">Status</th>
                <th class="border px-2 py-1">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr class="text-center">
                <td class="border px-2 py-1">{{ $loop->iteration + ($payments->currentPage()-1)*$payments->perPage() }}</td>
                <td class="border px-2 py-1">#{{ $payment->order_id }}</td>
                <td class="border px-2 py-1">{{ $payment->order->user->name ?? 'N/A' }}</td>
                <td class="border px-2 py-1">${{ number_format($payment->amount, 2) }}</td>
                <td class="border px-2 py-1">{{ $payment->method }}</td>
                <td class="border px-2 py-1">{{ ucfirst($payment->status) }}</td>
                <td class="border px-2 py-1 flex justify-center gap-2">
                    <a href="{{ route('admin.payments.edit', $payment) }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">Edit</a>
                    <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>

</div>
@endsection
