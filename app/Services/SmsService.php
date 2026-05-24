<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;

class SmsService
{
    public function send(string $to, string $message): void
    {
        $client = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token'),
        );

        $client->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }
}
