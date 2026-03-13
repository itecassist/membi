<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use App\Domain\Payment\Models\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * List all payment methods for this organisation.
     */
    public function index(Organisation $organisation): JsonResponse
    {
        abort_unless(request()->member->can('subscription.read'), 403);

        $methods = PaymentMethod::where('organisation_id', $organisation->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => PaymentMethodResource::collection($methods)]);
    }

    /**
     * Create a new payment method for this organisation.
     */
    public function store(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('subscription.create'), 403);

        $validated = $request->validate([
            'type'                      => ['required', 'string', 'in:bank_transfer,cheque,cash,standing_order,direct_debit,online_card'],
            'class'                     => ['required', 'in:one_off,recurring_arrears,recurring_advance'],
            'name'                      => ['required', 'string', 'max:255'],
            'explanation'               => ['required', 'string'],
            'checkout_text'             => ['nullable', 'string'],
            'success_text'              => ['nullable', 'string'],
            'is_active'                 => ['boolean'],
            'is_default'                => ['boolean'],
            'admin_only'                => ['boolean'],
            'requires_confirmation'     => ['boolean'],
            'surcharge_percentage'      => ['numeric', 'min:0'],
            'surcharge_fixed'           => ['numeric', 'min:0'],
            'payment_gateway_config_id' => ['nullable', 'uuid'],
        ]);

        // Ensure any referenced gateway config belongs to this org
        if (! empty($validated['payment_gateway_config_id'])) {
            $config = PaymentGatewayConfig::where('id', $validated['payment_gateway_config_id'])
                ->where('organisation_id', $organisation->id)
                ->firstOrFail();
        }

        // If this is being set as default, clear the current default
        if (! empty($validated['is_default'])) {
            PaymentMethod::where('organisation_id', $organisation->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $method = PaymentMethod::create(array_merge(
            $validated,
            ['organisation_id' => $organisation->id]
        ));

        return response()->json(['data' => new PaymentMethodResource($method), 'message' => 'Payment method created.'], 201);
    }

    /**
     * Show a single payment method.
     */
    public function show(Organisation $organisation, PaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless($paymentMethod->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        return response()->json(['data' => new PaymentMethodResource($paymentMethod)]);
    }

    /**
     * Update a payment method.
     */
    public function update(Request $request, Organisation $organisation, PaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless($paymentMethod->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $validated = $request->validate([
            'type'                  => ['sometimes', 'string', 'in:bank_transfer,cheque,cash,standing_order,direct_debit,online_card'],
            'class'                 => ['sometimes', 'in:one_off,recurring_arrears,recurring_advance'],
            'name'                  => ['sometimes', 'string', 'max:255'],
            'explanation'           => ['sometimes', 'string'],
            'checkout_text'         => ['nullable', 'string'],
            'success_text'          => ['nullable', 'string'],
            'is_active'             => ['boolean'],
            'is_default'            => ['boolean'],
            'admin_only'            => ['boolean'],
            'requires_confirmation' => ['boolean'],
            'surcharge_percentage'  => ['numeric', 'min:0'],
            'surcharge_fixed'       => ['numeric', 'min:0'],
        ]);

        if (! empty($validated['is_default'])) {
            PaymentMethod::where('organisation_id', $organisation->id)
                ->where('is_default', true)
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($validated);

        return response()->json(['data' => new PaymentMethodResource($paymentMethod->fresh()), 'message' => 'Payment method updated.']);
    }

    /**
     * Delete a payment method.
     */
    public function destroy(Organisation $organisation, PaymentMethod $paymentMethod): JsonResponse
    {
        abort_unless($paymentMethod->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.delete'), 403);

        $paymentMethod->delete();

        return response()->json(['message' => 'Payment method deleted.']);
    }
}
