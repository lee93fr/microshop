<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string   $to,
        public readonly Mailable $mail,
        public readonly string   $label = 'notification',
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->to)->send($this->mail);
        } catch (\Throwable $e) {
            Log::warning("Email {$this->label} non envoyé à {$this->to} : {$e->getMessage()}");
        }
    }
}
