@extends('layouts.app')

@section('title', 'Returns & Refunds')

@section('content')
<div class="max-w-5xl mx-auto p-6 bg-white rounded shadow mt-6">
    <h1 class="text-3xl font-bold mb-6">Returns & Refunds</h1>

    <p class="mb-4">We value your satisfaction. If you are not completely satisfied with your purchase, you can return it under the following conditions:</p>

    <h2 class="text-2xl font-semibold mt-6 mb-2">Eligibility</h2>
    <ul class="list-disc list-inside mb-4">
        <li>Items must be returned within 14 days of delivery.</li>
        <li>Products must be unused and in their original packaging.</li>
        <li>Proof of purchase (invoice or order number) is required.</li>
    </ul>

    <h2 class="text-2xl font-semibold mt-6 mb-2">How to Return</h2>
    <p class="mb-4">To initiate a return, please contact our support team at <a href="mailto:support@deluxeplus.com" class="text-blue-600 underline">support@deluxeplus.com</a> with your order details.</p>

    <h2 class="text-2xl font-semibold mt-6 mb-2">Refunds</h2>
    <p class="mb-4">Once your return is received and inspected, we will notify you of the approval or rejection of your refund. Approved refunds will be processed within 5-7 business days to your original payment method.</p>

    <h2 class="text-2xl font-semibold mt-6 mb-2">Exchanges</h2>
    <p class="mb-4">If you wish to exchange a product, please contact our support team to discuss available options.</p>

    <p class="mt-6">For any questions regarding returns, feel free to reach out to us at <a href="mailto:support@deluxeplus.com" class="text-blue-600 underline">support@deluxeplus.com</a>.</p>
</div>
@endsection
