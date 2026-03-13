<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\MemberSubscription;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionAutoRenewal;
use App\Domain\Subscription\Models\SubscriptionPriceLateFee;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use App\Domain\Subscription\Models\SubscriptionPriceOptionNewMember;
use App\Domain\Subscription\Models\SubscriptionPriceRenewal;
use App\Domain\Subscription\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionPriceOptionRequest;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionAutoRenewalResource;
use App\Http\Resources\SubscriptionPriceLateFeeResource;
use App\Http\Resources\SubscriptionPriceOptionNewMemberResource;
use App\Http\Resources\SubscriptionPriceOptionResource;
use App\Http\Resources\SubscriptionPriceRenewalResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $service) {}

    // ── Subscription types ────────────────────────────────────────────────────

    public function stats(Organisation $organisation): JsonResponse
    {
        abort_unless(request()->member->can('subscription.read'), 403);

        $now           = now()->toDateString();
        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        $subscriptions = Subscription::where('organisation_id', $organisation->id)
            ->orderBy('name')
            ->get();

        $breakdown = $subscriptions->map(function (Subscription $sub) use ($now, $thirtyDaysAgo) {
            $active = MemberSubscription::where('subscription_id', $sub->id)
                ->where('status', 'active')
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                });

            return [
                'id'               => $sub->id,
                'name'             => $sub->name,
                'membership_type'  => $sub->membership_type,
                'period'           => $sub->period,
                'published'        => $sub->published,
                'active_count'     => (clone $active)->count(),
                'members_count'    => (clone $active)->distinct('member_id')->count('member_id'),
                'auto_renew'       => (clone $active)->where('renewal_type', 'auto_renew')->count(),
                'manual_renew'     => (clone $active)->where('renewal_type', 'manual')->count(),
                'not_renewable'    => (clone $active)->where('renewal_type', 'not_renewable')->count(),
                'recently_expired' => MemberSubscription::where('subscription_id', $sub->id)
                    ->where('status', 'expired')
                    ->where('ends_at', '>=', $thirtyDaysAgo)
                    ->count(),
            ];
        });

        $membershipSubIds = $subscriptions->where('membership_type', 'individual')->pluck('id');
        $otherSubIds      = $subscriptions->where('membership_type', 'group')->pluck('id');

        $baseActive = fn () => MemberSubscription::where('organisation_id', $organisation->id)
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        return response()->json([
            'data' => [
                'summary' => [
                    'membership_subscriptions' => ($baseActive)()->whereIn('subscription_id', $membershipSubIds)->count(),
                    'other_subscriptions'      => ($baseActive)()->whereIn('subscription_id', $otherSubIds)->count(),
                    'members'                  => ($baseActive)()->distinct('member_id')->count('member_id'),
                    'expired_last_30_days'     => MemberSubscription::where('organisation_id', $organisation->id)
                        ->where('status', 'expired')
                        ->where('ends_at', '>=', $thirtyDaysAgo)
                        ->count(),
                    'pending_payment'          => MemberSubscription::where('organisation_id', $organisation->id)
                        ->where('status', 'pending')
                        ->count(),
                ],
                'breakdown' => $breakdown->values(),
            ],
        ]);
    }

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

    // ── Renewal settings ──────────────────────────────────────────────────────

    public function showRenewalSettings(Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        $renewal = $subscription->priceRenewal()->firstOrCreate(['subscription_id' => $subscription->id]);

        return response()->json(['data' => new SubscriptionPriceRenewalResource($renewal)]);
    }

    public function updateRenewalSettings(Request $request, Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $validated = $request->validate([
            'schedule_late_fees'  => ['sometimes', 'boolean'],
            'late_fee_option'     => ['nullable', 'string', 'max:50'],
            'late_fee_percentage' => ['nullable', 'numeric', 'min:0'],
            'renewal_day_month'   => ['nullable', 'string', 'max:10'],
        ]);

        $renewal = $subscription->priceRenewal()->updateOrCreate(
            ['subscription_id' => $subscription->id],
            $validated
        );

        return response()->json(['data' => new SubscriptionPriceRenewalResource($renewal), 'message' => 'Renewal settings updated.']);
    }

    // ── Auto-renewal settings ─────────────────────────────────────────────────

    public function showAutoRenewalSettings(Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        $autoRenewal = $subscription->autoRenewal()->firstOrCreate(['subscription_id' => $subscription->id]);

        return response()->json(['data' => new SubscriptionAutoRenewalResource($autoRenewal)]);
    }

    public function updateAutoRenewalSettings(Request $request, Organisation $organisation, Subscription $subscription): JsonResponse
    {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $validated = $request->validate([
            'enable_auto_renewal'            => ['sometimes', 'boolean'],
            'apply_to_all_subscription_fees' => ['sometimes', 'boolean'],
            'payment_method_id'              => ['nullable', 'uuid', 'exists:payment_methods,id'],
            'order_expiry_days'              => ['nullable', 'integer', 'min:1'],
            'should_have_form'               => ['sometimes', 'boolean'],
            'virtual_form_id'                => ['nullable', 'uuid', 'exists:virtual_forms,id'],
            'message'                        => ['nullable', 'string'],
        ]);

        $autoRenewal = $subscription->autoRenewal()->updateOrCreate(
            ['subscription_id' => $subscription->id],
            $validated
        );

        return response()->json(['data' => new SubscriptionAutoRenewalResource($autoRenewal), 'message' => 'Auto-renewal settings updated.']);
    }

    // ── New member settings (per price option) ────────────────────────────────

    public function showNewMemberSettings(
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        $settings = $priceOption->newMemberSettings()->firstOrCreate(['subscription_price_option_id' => $priceOption->id]);

        return response()->json(['data' => new SubscriptionPriceOptionNewMemberResource($settings)]);
    }

    public function updateNewMemberSettings(
        Request $request,
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless($request->member->can('subscription.update'), 403);

        $validated = $request->validate([
            'enable_rollover'         => ['sometimes', 'boolean'],
            'rollover_period_days'    => ['nullable', 'integer', 'min:0'],
            'enable_pro_rata_pricing' => ['sometimes', 'boolean'],
            'pro_rata_pricing'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $settings = $priceOption->newMemberSettings()->updateOrCreate(
            ['subscription_price_option_id' => $priceOption->id],
            $validated
        );

        return response()->json(['data' => new SubscriptionPriceOptionNewMemberResource($settings), 'message' => 'New member settings updated.']);
    }

    // ── Late fees (per price option) ──────────────────────────────────────────

    public function indexLateFees(
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): AnonymousResourceCollection {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless(request()->member->can('subscription.read'), 403);

        return SubscriptionPriceLateFeeResource::collection($priceOption->lateFees()->orderBy('applies_from')->get());
    }

    public function storeLateFee(
        Request $request,
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless($request->member->can('subscription.create'), 403);

        $validated = $request->validate([
            'price'        => ['required', 'numeric', 'min:0'],
            'renewal_date' => ['required', 'date'],
            'late_fee'     => ['required', 'numeric', 'min:0'],
            'applies_from' => ['required', 'date'],
        ]);

        $fee = $priceOption->lateFees()->create($validated);

        return response()->json(['data' => new SubscriptionPriceLateFeeResource($fee), 'message' => 'Late fee created.'], 201);
    }

    public function destroyLateFee(
        Organisation $organisation,
        Subscription $subscription,
        SubscriptionPriceOption $priceOption,
        SubscriptionPriceLateFee $lateFee
    ): JsonResponse {
        abort_unless($subscription->organisation_id === $organisation->id, 404);
        abort_unless($priceOption->subscription_id === $subscription->id, 404);
        abort_unless($lateFee->subscription_price_option_id === $priceOption->id, 404);
        abort_unless(request()->member->can('subscription.delete'), 403);

        $lateFee->delete();

        return response()->json(['message' => 'Late fee deleted.']);
    }
}
