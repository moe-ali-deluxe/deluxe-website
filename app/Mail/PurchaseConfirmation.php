<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase Confirmation - Order #'.$this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            // Use markdown for a nice layout (or switch to 'view' if you prefer)
            markdown: 'emails.purchase_confirmation',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
