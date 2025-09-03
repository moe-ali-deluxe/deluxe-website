<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('order')->orderBy('created_at', 'desc')->paginate(20);
        $methods = Payment::METHODS;
        $statuses = Payment::STATUSES;

        return view('admin.payments.index', compact('payments', 'methods', 'statuses'));
    }

    public function create()
    {
        // Only pending/partially_paid orders
        $orders = Order::whereNotIn('status', ['completed', 'cancelled'])->get();
        $methods = Payment::METHODS;
        $statuses = Payment::STATUSES;

        // Remaining balances (include ALL payments)
        $ordersJS = $orders->mapWithKeys(fn($o) => [$o->id => $o->remainingBalance()]);

        return view('admin.payments.create', compact('orders', 'methods', 'statuses', 'ordersJS'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:' . implode(',', Payment::METHODS),
            'status' => 'required|in:' . implode(',', Payment::STATUSES),
            'transaction_id' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $remaining = $order->remainingBalance(); // include all payments

        if (bccomp($validated['amount'], $remaining, 2) === 1) {
            return back()->withErrors([
                'amount' => "Payment exceeds remaining balance. Remaining: {$remaining}"
            ])->withInput();
        }

        $order->payments()->create($validated);
        $this->updateOrderStatus($order);

        return redirect()->route('admin.payments.index')->with('success', 'Payment created successfully.');
    }

    public function edit(Payment $payment)
    {
        // Show all orders but keep current order even if completed/cancelled
        $orders = Order::whereNotIn('status', ['completed', 'cancelled'])
                       ->orWhere('id', $payment->order_id)
                       ->get();

        $methods = Payment::METHODS;
        $statuses = Payment::STATUSES;

        // Remaining balance for this order EXCLUDING this payment
        $remaining = $payment->order->remainingBalance($payment->id);

        // JS map of remaining balances, exclude only this payment if same order
        $ordersJS = $orders->mapWithKeys(function ($o) use ($payment) {
            $excludeId = $o->id === $payment->order_id ? $payment->id : null;
            return [$o->id => $o->remainingBalance($excludeId)];
        });

        return view('admin.payments.edit', compact(
            'payment', 'orders', 'methods', 'statuses', 'remaining', 'ordersJS'
        ));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:' . implode(',', Payment::METHODS),
            'status' => 'required|in:' . implode(',', Payment::STATUSES),
            'transaction_id' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Remaining balance EXCLUDING this payment
        $remaining = $order->remainingBalance($payment->id);

        if (bccomp($validated['amount'], $remaining, 2) === 1) {
            return back()->withErrors([
                'amount' => "Payment exceeds remaining balance. Remaining: {$remaining}"
            ])->withInput();
        }

        $payment->update($validated);
        $this->updateOrderStatus($order);

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $order = $payment->order;
        $payment->delete();
        $this->updateOrderStatus($order);

        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted successfully.');
    }

    private function updateOrderStatus(Order $order)
    {
        $remaining = $order->remainingBalance();

        if (bccomp($remaining, 0, 2) === 0) {
            $order->update(['status' => 'completed']);
        } elseif (bccomp($remaining, $order->total_amount, 2) === -1) {
            $order->update(['status' => 'partially_paid']);
        } else {
            $order->update(['status' => 'pending']);
        }
    }
}
