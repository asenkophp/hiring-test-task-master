<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymentGateway
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $apiKey,
    ) {
    }

    public function charge(float $amount, string $currency, array $metadata = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(5)
            ->acceptJson()
            ->post($this->endpoint . '/charges', [
                'amount'   => $amount,
                'currency' => $currency,
                'meta'     => $metadata,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf('Gateway responded with %d: %s', $response->status(), $response->body())
            );
        }

        $reference = $response->json('reference');

        if (! is_string($reference) || $reference === '') {
            throw new RuntimeException('Gateway did not return a reference id.');
        }

        return $reference;
    }

    public function refund(string $reference): bool
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(5)
            ->acceptJson()
            ->post($this->endpoint . '/refunds', ['reference' => $reference]);

        return $response->successful();
    }
}
