<?php

namespace Database\Seeders;

use App\Domain\Organisation\Models\Organisation;
use App\Domain\Payment\Models\PaymentGatewayConfig;
use App\Domain\Payment\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = Organisation::all();

        foreach ($organisations as $org) {
            // ── Offline methods (no gateway required) ────────────────────────
            $offlineMethods = [
                [
                    'type'        => 'bank_transfer',
                    'class'       => 'one_off',
                    'name'        => 'Bank Transfer',
                    'explanation' => 'Pay directly into our bank account. Your membership will be activated once payment is confirmed.',
                    'checkout_text' => 'Please transfer the exact amount to: Sort Code 12-34-56, Account 12345678, Reference: your member number.',
                    'success_text'  => 'Thank you. Please send your bank transfer using the details shown. We will confirm your membership by email.',
                    'is_default'    => false,
                    'admin_only'    => false,
                    'requires_confirmation' => true,
                ],
                [
                    'type'        => 'cheque',
                    'class'       => 'one_off',
                    'name'        => 'Cheque',
                    'explanation' => 'Pay by cheque made payable to the club.',
                    'checkout_text' => 'Please make cheques payable to the club name and post to our address.',
                    'success_text'  => 'Thank you. Your membership will be activated once we receive and clear your cheque.',
                    'is_default'    => false,
                    'admin_only'    => false,
                    'requires_confirmation' => true,
                ],
                [
                    'type'        => 'cash',
                    'class'       => 'one_off',
                    'name'        => 'Cash',
                    'explanation' => 'Pay in cash at the club.',
                    'checkout_text' => 'Bring exact cash to the club office during opening hours.',
                    'success_text'  => 'Thank you. Please bring your cash payment to the club office.',
                    'is_default'    => false,
                    'admin_only'    => true,
                    'requires_confirmation' => true,
                ],
            ];

            foreach ($offlineMethods as $data) {
                PaymentMethod::firstOrCreate(
                    ['organisation_id' => $org->id, 'type' => $data['type']],
                    array_merge($data, [
                        'is_active'            => true,
                        'surcharge_percentage' => 0,
                        'surcharge_fixed'      => 0,
                    ])
                );
            }

            // ── GoCardless Direct Debit (only if a sandbox config exists) ────
            $gcConfig = PaymentGatewayConfig::where('organisation_id', $org->id)
                ->where('type', 'gocardless')
                ->where('is_active', true)
                ->first();

            if ($gcConfig) {
                PaymentMethod::firstOrCreate(
                    ['organisation_id' => $org->id, 'type' => 'direct_debit'],
                    [
                        'payment_gateway_config_id' => $gcConfig->id,
                        'class'       => 'recurring_arrears',
                        'name'        => 'Direct Debit',
                        'explanation' => 'Set up a GoCardless Direct Debit for automatic annual renewal.',
                        'checkout_text' => 'You will be redirected to GoCardless to set up your Direct Debit mandate.',
                        'success_text'  => 'Your Direct Debit mandate has been set up. Your first payment will be collected shortly.',
                        'is_active'   => true,
                        'is_default'  => true,
                        'admin_only'  => false,
                        'requires_confirmation' => false,
                        'surcharge_percentage'  => 0,
                        'surcharge_fixed'       => 0,
                    ]
                );
            }

            // ── WorldPay Card (only if a config exists) ───────────────────────
            $wpConfig = PaymentGatewayConfig::where('organisation_id', $org->id)
                ->where('type', 'worldpay')
                ->where('is_active', true)
                ->first();

            if ($wpConfig) {
                PaymentMethod::firstOrCreate(
                    ['organisation_id' => $org->id, 'type' => 'online_card'],
                    [
                        'payment_gateway_config_id' => $wpConfig->id,
                        'class'       => 'one_off',
                        'name'        => 'Card Payment',
                        'explanation' => 'Pay by debit or credit card via WorldPay.',
                        'checkout_text' => 'You will be redirected to our secure card payment page.',
                        'success_text'  => 'Your card payment was successful. Your membership is now active.',
                        'is_active'   => true,
                        'is_default'  => false,
                        'admin_only'  => false,
                        'requires_confirmation' => false,
                        'surcharge_percentage'  => 0,
                        'surcharge_fixed'       => 0,
                    ]
                );
            }
        }

        $this->command->info('Payment methods seeded.');
    }
}
