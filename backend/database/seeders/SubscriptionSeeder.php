<?php

namespace Database\Seeders;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Models\SubscriptionPriceOption;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $tennis  = Organisation::where('seo_name', 'river')->firstOrFail();
        $cycling = Organisation::where('seo_name', 'north')->firstOrFail();

        // ── Riverside Tennis Club ─────────────────────────────────────────────

        // Annual membership — flat pricing with adult/junior/family tiers
        $annualMembership = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Annual Membership'],
            [
                'description'    => 'Full playing membership for the calendar year.',
                'is_membership'  => true,
                'period_type'    => 'year',
                'renewal_type'   => 'manual',
                'pricing_type'   => 'flat',
                'published'      => 'published',
                'is_joining_fee' => false,
            ]
        );

        foreach ([
            ['label' => 'Adult',  'eligibility' => 'adult',      'price' => 120.00],
            ['label' => 'Junior', 'eligibility' => 'junior',     'price' => 45.00],
            ['label' => 'Senior', 'eligibility' => 'individual', 'price' => 80.00],
        ] as $option) {
            SubscriptionPriceOption::firstOrCreate(
                ['subscription_id' => $annualMembership->id, 'label' => $option['label']],
                [
                    'eligibility'  => $option['eligibility'],
                    'pricing_type' => 'flat',
                    'price'        => $option['price'],
                    'published'    => 'published',
                ]
            );
        }

        // Family membership — family pricing type
        $familyMembership = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Family Membership'],
            [
                'description'    => 'Covers up to 2 adults + dependent children.',
                'is_membership'  => true,
                'period_type'    => 'year',
                'renewal_type'   => 'auto_renew',
                'pricing_type'   => 'family',
                'published'      => 'published',
                'is_joining_fee' => false,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $familyMembership->id, 'label' => 'Family'],
            [
                'eligibility'  => 'family',
                'pricing_type' => 'flat',
                'price'        => 220.00,
                'published'    => 'published',
            ]
        );

        // One-off joining fee
        $joiningFee = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Joining Fee'],
            [
                'description'    => 'One-time fee for new members.',
                'is_membership'  => false,
                'period_type'    => 'none',
                'renewal_type'   => 'not_renewable',
                'pricing_type'   => 'flat',
                'published'      => 'published',
                'is_joining_fee' => true,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $joiningFee->id, 'label' => 'Standard'],
            [
                'eligibility'  => 'individual',
                'pricing_type' => 'flat',
                'price'        => 25.00,
                'published'    => 'published',
            ]
        );

        // ── Northside Cycling Club ────────────────────────────────────────────

        $cyclingAnnual = Subscription::firstOrCreate(
            ['organisation_id' => $cycling->id, 'name' => 'Annual Membership'],
            [
                'description'    => 'Annual club membership with British Cycling affiliation.',
                'is_membership'  => true,
                'period_type'    => 'year',
                'renewal_type'   => 'auto_renew',
                'pricing_type'   => 'flat',
                'published'      => 'published',
                'is_joining_fee' => false,
            ]
        );

        foreach ([
            ['label' => 'Adult',         'eligibility' => 'adult',      'price' => 55.00],
            ['label' => 'Junior',        'eligibility' => 'junior',     'price' => 20.00],
            ['label' => 'Concessionary', 'eligibility' => 'individual', 'price' => 30.00],
        ] as $option) {
            SubscriptionPriceOption::firstOrCreate(
                ['subscription_id' => $cyclingAnnual->id, 'label' => $option['label']],
                [
                    'eligibility'  => $option['eligibility'],
                    'pricing_type' => 'flat',
                    'price'        => $option['price'],
                    'published'    => 'published',
                ]
            );
        }

        // Monthly pay-as-you-go
        $monthly = Subscription::firstOrCreate(
            ['organisation_id' => $cycling->id, 'name' => 'Monthly Rolling'],
            [
                'description'    => 'Pay monthly, cancel anytime.',
                'is_membership'  => true,
                'period_type'    => 'month',
                'renewal_type'   => 'auto_renew',
                'pricing_type'   => 'flat',
                'published'      => 'published',
                'is_joining_fee' => false,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $monthly->id, 'label' => 'Monthly'],
            [
                'eligibility'  => 'individual',
                'pricing_type' => 'flat',
                'price'        => 6.00,
                'published'    => 'published',
            ]
        );

        $this->command->info('Subscriptions seeded.');
    }
}
