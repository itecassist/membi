<?php

namespace App\Http\Controllers\Api;

use App\Domain\Checkout\Models\Basket;
use App\Domain\Checkout\Models\BasketItem;
use App\Domain\Checkout\Services\CheckoutService;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\BasketItemResource;
use App\Http\Resources\BasketResource;
use App\Http\Resources\OrderPaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    // ── Basket ────────────────────────────────────────────────────────────────

    public function createBasket(Request $request, Organisation $organisation): JsonResponse
    {
        $basket = $this->checkout->createBasket(
            $organisation,
            $request->member,
            $request->member ? 'member' : 'public'
        );

        $basket->load('items.subscription', 'items.priceOption');

        return response()->json(['data' => new BasketResource($basket)], 201);
    }

    public function showBasket(Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $state = $this->checkout->getState($basket);

        return response()->json(['data' => $state]);
    }

    public function addItem(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $validated = $request->validate([
            'subscription_price_option_id' => ['required', 'uuid', 'exists:subscription_price_options,id'],
            'quantity'                     => ['integer', 'min:1', 'max:100'],
        ]);

        $item = $this->checkout->addItem($basket, $validated['subscription_price_option_id'], $validated['quantity'] ?? 1);
        $item->load('subscription', 'priceOption');

        return response()->json([
            'data'    => new BasketItemResource($item),
            'message' => 'Item added to basket.',
        ], 201);
    }

    public function removeItem(Organisation $organisation, Basket $basket, BasketItem $item): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);
        $this->checkout->removeItem($basket, $item);

        return response()->json(['message' => 'Item removed from basket.']);
    }

    // ── Checkout session ─────────────────────────────────────────────────────

    public function startCheckout(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $session = $this->checkout->startCheckout($basket, $request->member ? 'member' : 'public');

        return response()->json(['data' => $session]);
    }

    public function captureEmail(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $validated = $request->validate(['email' => ['required', 'email', 'max:255']]);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $session = $this->checkout->captureEmail($session, $validated['email']);

        return response()->json(['data' => $session]);
    }

    public function setAllocations(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $validated = $request->validate([
            'allocations'               => ['required', 'array'],
            'allocations.*.basket_item_id' => ['required', 'uuid'],
            'allocations.*.member_id'   => ['nullable', 'uuid', 'exists:members,id'],
            'allocations.*.group_id'    => ['nullable', 'uuid', 'exists:groups,id'],
        ]);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $session = $this->checkout->setAllocations($session, $validated['allocations']);

        return response()->json(['data' => $session]);
    }

    public function setFormData(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $validated = $request->validate([
            'forms' => ['required', 'array'],
        ]);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $session = $this->checkout->setFormData($session, $validated['forms']);

        return response()->json(['data' => $session]);
    }

    public function setPaymentMethod(Request $request, Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $validated = $request->validate([
            'payment_method_id' => ['required', 'uuid', 'exists:payment_methods,id'],
        ]);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $session = $this->checkout->setPaymentMethod($session, $validated['payment_method_id']);

        return response()->json(['data' => $session]);
    }

    public function createOrder(Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $order = $this->checkout->createOrder($session);
        $order->load('items', 'payments');

        return response()->json([
            'data'    => [
                'id'            => $order->id,
                'status'        => $order->status,
                'total'         => $order->total,
                'currency_code' => $order->currency_code,
                'date_placed'   => $order->date_placed,
                'payments'      => OrderPaymentResource::collection($order->payments),
            ],
            'message' => 'Order created.',
        ], 201);
    }

    public function finalizeCheckout(Organisation $organisation, Basket $basket): JsonResponse
    {
        abort_unless($basket->organisation_id === $organisation->id, 404);

        $session = $basket->checkoutSession;
        abort_unless($session, 422, 'No active checkout session.');

        $order = $this->checkout->finalizeCheckout($session);

        return response()->json([
            'data'    => [
                'id'            => $order->id,
                'status'        => $order->status,
                'total'         => $order->total,
                'currency_code' => $order->currency_code,
                'date_placed'   => $order->date_placed,
            ],
            'message' => 'Checkout complete.',
        ]);
    }

}
