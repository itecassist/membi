<?php

namespace App\Domain\Payment\Models;

use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'payment_gateway_config_id',
        'type',
        'class',
        'name',
        'explanation',
        'is_active',
        'is_default',
        'admin_only',
        'requires_confirmation',
        'surcharge_percentage',
        'surcharge_fixed',
        'accounting_code_id',
        'checkout_text',
        'success_text',
    ];

    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'is_default'             => 'boolean',
            'admin_only'             => 'boolean',
            'requires_confirmation'  => 'boolean',
            'surcharge_percentage'   => 'decimal:2',
            'surcharge_fixed'        => 'decimal:2',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function gatewayConfig(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayConfig::class, 'payment_gateway_config_id');
    }

    public function subscriptionPaymentMethods(): HasMany
    {
        return $this->hasMany(SubscriptionPaymentMethod::class);
    }

    public function memberPaymentMethods(): HasMany
    {
        return $this->hasMany(MemberPaymentMethod::class);
    }

    public function isOffline(): bool
    {
        return in_array($this->type, ['bank_transfer', 'cheque', 'cash', 'standing_order']);
    }

    public function isGateway(): bool
    {
        return in_array($this->type, ['direct_debit', 'online_card']);
    }
}
