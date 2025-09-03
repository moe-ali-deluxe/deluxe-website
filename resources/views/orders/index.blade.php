@extends('layouts.app')

@section('title', 'My Orders')
@section('canonical', route('orders.index'))
@section('robots', 'noindex,nofollow')

@section('content')
<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow mt-6">

    <h1 class="text-3xl font-bold mb-6">My Orders</h1>

    {{-- Success/Error messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <ul id="orders-tabs"
        class="flex border-b mb-6"
        role="tablist"
        aria-label="Orders tabs"
        data-url-completed="{{ route('orders.completed') }}"
        data-url-cancelled="{{ route('orders.cancelled') }}"
    >
        <li class="-mb-px mr-1" role="presentation">
            <a href="#active"
               role="tab"
               aria-selected="true"
               aria-controls="active"
               class="tab-link active py-2 px-4 font-semibold border-l border-t border-r rounded-t bg-white text-blue-600"
               data-target="active">
               Active Orders
            </a>
        </li>
        <li class="mr-1" role="presentation">
            <a href="#completed"
               role="tab"
               aria-selected="false"
               aria-controls="completed"
               class="tab-link inactive py-2 px-4 font-semibold border-l border-t border-r rounded-t bg-gray-100 text-gray-600 hover:text-blue-600"
               data-target="completed">
               Completed Orders
            </a>
        </li>
        <li class="mr-1" role="presentation">
            <a href="#cancelled"
               role="tab"
               aria-selected="false"
               aria-controls="cancelled"
               class="tab-link inactive py-2 px-4 font-semibold border-l border-t border-r rounded-t bg-gray-100 text-gray-600 hover:text-blue-600"
               data-target="cancelled">
               Cancelled Orders
            </a>
        </li>
    </ul>

    {{-- Tab contents --}}
    <div id="tab-content">
        {{-- Active Orders --}}
        <div class="tab-pane" id="active" role="tabpanel" aria-labelledby="Active Orders">
            @if($activeOrders->isEmpty())
                <p>No active orders found.</p>
            @else
                @foreach($activeOrders as $order)
                    <div class="mb-4 p-4 border rounded flex justify-between items-center">
                        <div>
                            <p><strong>Order #:</strong> {{ $order->id }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                            <p><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                            <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        @if(!in_array($order->status, ['completed', 'cancelled']))
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="cancel-order-form">
                            @csrf
                            <button type="submit"
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Completed Orders (AJAX) --}}
        <div class="tab-pane" id="completed" role="tabpanel" aria-labelledby="Completed Orders" style="display:none;" data-loaded="false"></div>

        {{-- Cancelled Orders (AJAX) --}}
        <div class="tab-pane" id="cancelled" role="tabpanel" aria-labelledby="Cancelled Orders" style="display:none;" data-loaded="false"></div>
    </div>

    {{-- Loader --}}
    <div id="loader" class="hidden fixed top-0 left-0 w-full h-full bg-black bg-opacity-30 items-center justify-center z-50">
        <div class="bg-white p-4 rounded shadow text-center">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Loading...
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs     = document.querySelectorAll('.tab-link');
    const panes    = document.querySelectorAll('.tab-pane');
    const loader   = document.getElementById('loader');
    const tabRoot  = document.getElementById('orders-tabs');
    const urlCompleted = tabRoot?.dataset.urlCompleted || '/my-orders/completed';
    const urlCancelled = tabRoot?.dataset.urlCancelled || '/my-orders/cancelled';

    function showLoader() {
        loader.classList.remove('hidden');
        loader.classList.add('flex');
    }
    function hideLoader() {
        loader.classList.add('hidden');
        loader.classList.remove('flex');
    }

    function setActiveTab(clicked) {
        tabs.forEach(t => {
            t.classList.remove('active');
            t.classList.add('inactive');
            t.classList.remove('bg-white', 'text-blue-600');
            t.classList.add('bg-gray-100', 'text-gray-600');
            t.setAttribute('aria-selected', 'false');
        });
        clicked.classList.add('active');
        clicked.classList.remove('inactive');
        clicked.classList.remove('bg-gray-100', 'text-gray-600');
        clicked.classList.add('bg-white', 'text-blue-600');
        clicked.setAttribute('aria-selected', 'true');
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.target;
            setActiveTab(this);

            // Hide panes
            panes.forEach(div => { div.style.display = 'none'; });

            const div = document.getElementById(target);

            if (target === 'active') {
                div.style.display = 'block';
                return;
            }

            // Determine URL by tab
            const url = (target === 'completed') ? urlCompleted : urlCancelled;

            // Load via AJAX if not loaded
            if (div.dataset.loaded === 'false') {
                showLoader();
                fetch(url, { headers: { 'Accept': 'text/html' }})
                    .then(response => response.text())
                    .then(html => {
                        div.innerHTML = html;
                        div.style.display = 'block';
                        div.dataset.loaded = 'true';
                        hideLoader();
                    })
                    .catch(() => {
                        div.innerHTML = '<p class="text-red-500">Failed to load orders.</p>';
                        div.style.display = 'block';
                        hideLoader();
                    });
            } else {
                div.style.display = 'block';
            }
        });
    });

    // Show Active Orders by default
    document.getElementById('active').style.display = 'block';

    // Disable cancel button while submitting; refresh other tabs after cancel
    document.querySelectorAll('.cancel-order-form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Cancelling...';
                btn.classList.add('opacity-70', 'cursor-not-allowed');
            }
            // After request completes server-side, index reloads; if it doesn't,
            // force other tabs to reload next time they are opened:
            setTimeout(() => {
                const completed = document.getElementById('completed');
                const cancelled = document.getElementById('cancelled');
                if (completed) completed.dataset.loaded = 'false';
                if (cancelled) cancelled.dataset.loaded = 'false';
            }, 1000);
        });
    });
});
</script>
@endsection
