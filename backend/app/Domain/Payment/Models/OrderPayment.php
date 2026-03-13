<?php

namespace App\Domain\Payment\Models;

use App\Domain\Member\Models\Member;
use App\Domain\Order\Models\Order;
use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OrderPayment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'organisation_id',
        'member_id',
        'payment_method_id',
        'is_manual',
        'requires_confirmation',
        'is_renewal',
        'currency_code',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
        'tracking_token',
        'gateway_customer_id',
        'gateway_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'is_manual'             => 'boolean',
            'requires_confirmation' => 'boolean',
            'is_renewal'            => 'boolean',
            'amount_due'            => 'decimal:2',
            'amount_paid'           => 'decimal:2',
            'due_date'              => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (empty($payment->tracking_token)) {
                $payment->tracking_token = Str::uuid()->toString();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isFullyPaid(): bool
    {
        return bccomp((string) $this->amount_paid, (string) $this->amount_due, 6) >= 0;
    }
}
