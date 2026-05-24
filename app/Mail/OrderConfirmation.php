<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\NotificationTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    private array $tpl;

    public function __construct(public readonly Order $order)
    {
        $this->tpl = (new NotificationTemplateService)->render('order_confirmation', $order);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->tpl['subject']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.custom',
            with: ['body' => $this->tpl['body'], 'headerColor' => $this->tpl['header_color'], 'shopName' => $this->tpl['shop_name']],
        );
    }
}
