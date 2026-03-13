<?php

namespace App\Http\Controllers\Api;

use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    /**
     * Receive GoCardless webhook events.
     * POST /api/webhooks/gocardless/{organisation}
     */
    public function gocardless(Request $request, string $organisationId): Response
    {
        $config = PaymentGatewayConfig::where('organisation_id', $organisationId)
            ->where('type', 'gocardless')
            ->where('is_active', true)
            ->firstOrFail();

        $driver = $this->payments->resolveDriverFromConfig($config);
        $driver->handleWebhook($request->getContent(), $request->headers->all());

        return response()->noContent();
    }

    /**
     * Receive WorldPay webhook notifications.
     * POST /api/webhooks/worldpay/{organisation}
     */
    public function worldpay(Request $request, string $organisationId): Response
    {
        $config = PaymentGatewayConfig::where('organisation_id', $organisationId)
            ->where('type', 'worldpay')
            ->where('is_active', true)
            ->firstOrFail();

        $driver = $this->payments->resolveDriverFromConfig($config);
        $driver->handleWebhook($request->getContent(), $request->headers->all());

        return response()->noContent();
    }
}
