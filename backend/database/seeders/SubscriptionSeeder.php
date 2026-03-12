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
        $tennis  = Organisation::where('seo_name', 'riverside-tennis')->firstOrFail();
        $cycling = Organisation::where('seo_name', 'northside-cycling')->firstOrFail();

        // ── Riverside Tennis Club ─────────────────────────────────────────────

        // Annual membership — flat pricing with adult/junior/family tiers
        $annualMembership = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Annual Membership'],
            [
                'description'    => 'Full playing membership for the calendar year.',
                'membership_type' => 'individual',
                'period'         => 'year',
                'renewal_type'   => 'manual',
                'pricing_type'   => 'flat',
                'published'      => true,
                'is_joining_fee' => false,
            ]
        );

        foreach ([
            ['name' => 'Adult',  'eligibility' => 'adult',      'price' => 120.00],
            ['name' => 'Junior', 'eligibility' => 'junior',     'price' => 45.00],
            ['name' => 'Senior', 'eligibility' => 'individual', 'price' => 80.00],
        ] as $option) {
            SubscriptionPriceOption::firstOrCreate(
                ['subscription_id' => $annualMembership->id, 'name' => $option['name']],
                [
                    'eligibility'  => $option['eligibility'],
                    'pricing_type' => 'flat',
                    'price'        => $option['price'],
                    'published'    => true,
                ]
            );
        }

        // Family membership — family pricing type
        $familyMembership = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Family Membership'],
            [
                'description'     => 'Covers up to 2 adults + dependent children.',
                'membership_type' => 'group',
                'period'          => 'year',
                'renewal_type'    => 'auto_renew',
                'pricing_type'    => 'family',
                'published'       => true,
                'is_joining_fee'  => false,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $familyMembership->id, 'name' => 'Family'],
            [
                'eligibility'  => 'family',
                'pricing_type' => 'flat',
                'price'        => 220.00,
                'published'    => true,
            ]
        );

        // One-off joining fee
        $joiningFee = Subscription::firstOrCreate(
            ['organisation_id' => $tennis->id, 'name' => 'Joining Fee'],
            [
                'description'    => 'One-time fee for new members.',
                'membership_type' => 'individual',
                'period'         => 'none',
                'renewal_type'   => 'not_renewable',
                'pricing_type'   => 'flat',
                'published'      => true,
                'is_joining_fee' => true,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $joiningFee->id, 'name' => 'Standard'],
            [
                'eligibility'  => 'individual',
                'pricing_type' => 'flat',
                'price'        => 25.00,
                'published'    => true,
            ]
        );

        // ── Northside Cycling Club ────────────────────────────────────────────

        $cyclingAnnual = Subscription::firstOrCreate(
            ['organisation_id' => $cycling->id, 'name' => 'Annual Membership'],
            [
                'description'    => 'Annual club membership with British Cycling affiliation.',
                'membership_type' => 'individual',
                'period'         => 'year',
                'renewal_type'   => 'auto_renew',
                'pricing_type'   => 'flat',
                'published'      => true,
                'is_joining_fee' => false,
            ]
        );

        foreach ([
            ['name' => 'Adult',         'eligibility' => 'adult',      'price' => 55.00],
            ['name' => 'Junior',        'eligibility' => 'junior',     'price' => 20.00],
            ['name' => 'Concessionary', 'eligibility' => 'individual', 'price' => 30.00],
        ] as $option) {
            SubscriptionPriceOption::firstOrCreate(
                ['subscription_id' => $cyclingAnnual->id, 'name' => $option['name']],
                [
                    'eligibility'  => $option['eligibility'],
                    'pricing_type' => 'flat',
                    'price'        => $option['price'],
                    'published'    => true,
                ]
            );
        }

        // Monthly pay-as-you-go
        $monthly = Subscription::firstOrCreate(
            ['organisation_id' => $cycling->id, 'name' => 'Monthly Rolling'],
            [
                'description'    => 'Pay monthly, cancel anytime.',
                'membership_type' => 'individual',
                'period'         => 'month',
                'renewal_type'   => 'auto_renew',
                'pricing_type'   => 'flat',
                'published'      => true,
                'is_joining_fee' => false,
            ]
        );

        SubscriptionPriceOption::firstOrCreate(
            ['subscription_id' => $monthly->id, 'name' => 'Monthly'],
            [
                'eligibility'  => 'individual',
                'pricing_type' => 'flat',
                'price'        => 6.00,
                'published'    => true,
            ]
        );

        $this->command->info('Subscriptions seeded.');
    }
}
