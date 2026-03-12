<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use App\Domain\Subscription\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionPriceOptionRequest;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionPriceOptionResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $service) {}

    // ── Subscription types ────────────────────────────────────────────────────

    public function index(Organisation $organisation): AnonymousResourceCollection
    {
        abort_unless(request()->member->can('subscription.read'), 403);

        return SubscriptionResource::collection(
            $this->service->listForOrganisation($organisation)
        );
    }

    public function store(StoreSubscriptionRequest $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('subscription.create'), 403);

        $subscription = $this->service->create($organisation, $request->validated());

        return response()->json([
            'data'    => new SubscriptionResource($subscription),
            'message' => 'Subscription created',
        ], 201);
    }

    public function show(Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        $subscription->load('priceOptions');

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    public function update(StoreSubscriptionRequest $request, Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $subscription = $this->service->update($subscription, $request->validated());

        return response()->json([
            'data'    => new SubscriptionResource($subscription),
            'message' => 'Subscription updated',
        ]);
    }

    public function destroy(Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.delete'), 403);

        $this->service->delete($subscription);

        return response()->json(['message' => 'Subscription deleted']);
    }

    // ── Price options ─────────────────────────────────────────────────────────

    public function priceOptions(Organisation $organisation, Subscription $subscription): AnonymousResourceCollection
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        return SubscriptionPriceOptionResource::collection(
            $subscription->priceOptions()->paginate(50)
        );
    }

    public function storePriceOption(
        StoreSubscriptionPriceOptionRequest $request,
        Organisation $organisation,
        Subscription $subscription
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.create'), 403);

        $option = $this->service->createPriceOption($subscription, $request->validated());

        return response()->json([
            'data'    => new SubscriptionPriceOptionResource($option),
            'message' => 'Price option created',
        ], 201);
    }

    public function updatePriceOption(
        StoreSubscriptionPriceOptionRequest $request,
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $option = $this->service->updatePriceOption($priceOption, $request->validated());

        return response()->json([
            'data'    => new SubscriptionPriceOptionResource($option),
            'message' => 'Price option updated',
        ]);
    }

    public function destroyPriceOption(
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless(request()->member->can('subscription.delete'), 403);

        $this->service->deletePriceOption($priceOption);

        return response()->json(['message' => 'Price option deleted']);
    }
}
