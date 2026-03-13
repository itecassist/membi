<?php

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\Models\OrderPayment;

interface PaymentDriverInterface
{
    /**
     * Initiate payment for an order payment record.
     * Returns a redirect URL for hosted payment flows (GoCardless, WorldPay)
     * or null for offline/manual methods.
     */
    public function initiatePayment(OrderPayment $payment, array $options = []): ?string;

    /**
     * Handle an inbound webhook payload from the payment gateway.
     * Implementations should verify signatures and update OrderPayment records.
     */
    public function handleWebhook(string $payload, array $headers): void;

    /**
     * Cancel a previously initiated payment at the gateway level.
     */
    public function cancelPayment(OrderPayment $payment): void;
}
