<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Payment\Models\MemberPaymentMethod;
use App\Domain\Payment\Models\OrderPayment;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use App\Domain\Payment\Services\GoCardlessService;
use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberPaymentMethodResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    /**
     * Initiate payment for an existing order payment.
     * Returns a redirect URL for hosted flows (GoCardless, WorldPay).
     * POST /api/organisations/{organisation}/order-payments/{payment}/initiate
     */
    public function initiate(Request $request, Organisation $organisation, OrderPayment $payment): JsonResponse
    {
        abort_unless($payment->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.create'), 403);

        $validated = $request->validate([
            'success_url' => ['nullable', 'url'],
            'cancel_url'  => ['nullable', 'url'],
        ]);

        $redirectUrl = $this->payments->initiatePayment($payment, $validated);

        return response()->json([
            'data'         => ['redirect_url' => $redirectUrl, 'payment_id' => $payment->id],
            'message'      => $redirectUrl ? 'Redirect to payment gateway.' : 'Payment is manual — awaiting confirmation.',
        ]);
    }

    /**
     * List GoCardless mandates (stored billing methods) for a member.
     * GET /api/organisations/{organisation}/members/{memberId}/payment-methods
     */
    public function memberMethods(Request $request, Organisation $organisation, string $memberId): JsonResponse
    {
        abort_unless($request->member->can('member.read'), 403);

        $methods = MemberPaymentMethod::where('member_id', $memberId)
            ->with('paymentMethod')
            ->get();

        return response()->json(['data' => MemberPaymentMethodResource::collection($methods)]);
    }

    /**
     * Cancel / deactivate a stored member payment method (e.g. a GoCardless mandate).
     * DELETE /api/organisations/{organisation}/payment-methods/{memberMethod}
     */
    public function cancelMemberMethod(Request $request, Organisation $organisation, MemberPaymentMethod $memberMethod): JsonResponse
    {
        abort_unless($request->member->can('member.update'), 403);

        $paymentMethod = $memberMethod->paymentMethod;
        abort_unless($paymentMethod && $paymentMethod->organisation_id === $organisation->id, 404);

        if ($paymentMethod->type === 'direct_debit' && $memberMethod->gateway_reference) {
            $config = $paymentMethod->gatewayConfig;
            if ($config && $config->is_active) {
                $driver = new GoCardlessService($config);
                try {
                    $driver->cancelPayment(new \App\Domain\Payment\Models\OrderPayment([
                        'gateway_transaction_id' => $memberMethod->gateway_reference,
                    ]));
                } catch (\Throwable) {
                    // Log but don't fail — still deactivate locally
                }
            }
        }

        $memberMethod->update(['is_active' => false]);
        $memberMethod->delete();

        return response()->json(['message' => 'Payment method cancelled.']);
    }

    /**
     * Admin: configure gateway credentials for an organisation.
     * POST /api/organisations/{organisation}/gateway-configs
     */
    public function storeGatewayConfig(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('subscription.create'), 403);

        $validated = $request->validate([
            'type'      => ['required', 'in:gocardless,worldpay'],
            'is_active' => ['boolean'],
            'config'    => ['required', 'array'],
        ]);

        $gatewayConfig = PaymentGatewayConfig::updateOrCreate(
            ['organisation_id' => $organisation->id, 'type' => $validated['type']],
            ['is_active' => $validated['is_active'] ?? true]
        );
        $gatewayConfig->setEncryptedConfig($validated['config']);

        return response()->json([
            'data'    => ['id' => $gatewayConfig->id, 'type' => $gatewayConfig->type, 'is_active' => $gatewayConfig->is_active],
            'message' => 'Gateway configured.',
        ], 201);
    }
}
