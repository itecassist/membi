<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentDriverInterface;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Resolve the correct payment driver for a given OrderPayment and
     * return the redirect URL (for hosted flows) or null (offline methods).
     */
    public function initiatePayment(OrderPayment $payment, array $options = []): ?string
    {
        $driver = $this->resolveDriver($payment);

        if ($driver === null) {
            // Offline method — mark as pending manual confirmation
            $payment->update([
                'status'                 => 'pending',
                'requires_confirmation'  => true,
            ]);
            return null;
        }

        return $driver->initiatePayment($payment, $options);
    }

    public function cancelPayment(OrderPayment $payment): void
    {
        $driver = $this->resolveDriver($payment);
        $driver?->cancelPayment($payment);
    }

    /**
     * Resolve a driver from a raw PaymentGatewayConfig (used in webhook controllers).
     */
    public function resolveDriverFromConfig(PaymentGatewayConfig $config): PaymentDriverInterface
    {
        return match ($config->type) {
            'gocardless' => new GoCardlessService($config),
            'worldpay'   => new WorldPayService($config),
            default      => throw new \InvalidArgumentException("Unknown gateway type: {$config->type}"),
        };
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function resolveDriver(OrderPayment $payment): ?PaymentDriverInterface
    {
        $paymentMethod = $payment->paymentMethod;

        if (! $paymentMethod || $paymentMethod->isOffline()) {
            return null;
        }

        $gatewayConfig = $paymentMethod->gatewayConfig;

        if (! $gatewayConfig || ! $gatewayConfig->is_active) {
            Log::warning('Payment method has no active gateway config', ['payment_method_id' => $paymentMethod->id]);
            return null;
        }

        return $this->resolveDriverFromConfig($gatewayConfig);
    }
}
