<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use Illuminate\Pagination\LengthAwarePaginator;

class SubscriptionService
{
    public function listForOrganisation(Organisation $organisation, int $perPage = 15): LengthAwarePaginator
    {
        return $organisation->subscriptions()
            ->withCount('priceOptions')
            ->latest()
            ->paginate($perPage);
    }

    public function create(Organisation $organisation, array $data): Subscription
    {
        return $organisation->subscriptions()->create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->fresh();
    }

    public function delete(Subscription $subscription): void
    {
        $subscription->delete();
    }

    public function createPriceOption(Subscription $subscription, array $data): SubscriptionPriceOption
    {
        return $subscription->priceOptions()->create($data);
    }

    public function updatePriceOption(SubscriptionPriceOption $option, array $data): SubscriptionPriceOption
    {
        $option->update($data);

        return $option->fresh();
    }

    public function deletePriceOption(SubscriptionPriceOption $option): void
    {
        $option->delete();
    }
}
