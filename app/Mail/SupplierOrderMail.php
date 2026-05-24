<?php

namespace App\Mail;

use App\Models\SupplierOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SupplierOrder $supplierOrder,
        private readonly string       $pdfContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bon de commande {$this->supplierOrder->reference}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mails.supplier-order');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdfContent,
                "{$this->supplierOrder->reference}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
