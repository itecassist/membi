<?php

namespace App\Domain\Payment\Models;

use App\Domain\Member\Models\Member;
use App\Domain\Order\Models\Order;
use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'member_id',
        'order_id',
        'order_payment_id',
        'accounting_code_id',
        'description',
        'currency_code',
        'debit',
        'credit',
        'reconciled',
        'reconciled_at',
        'synced_to_finance',
        'finance_reference',
    ];

    protected function casts(): array
    {
        return [
            'debit'             => 'decimal:6',
            'credit'            => 'decimal:6',
            'reconciled'        => 'boolean',
            'synced_to_finance' => 'boolean',
            'reconciled_at'     => 'datetime',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPayment(): BelongsTo
    {
        return $this->belongsTo(OrderPayment::class);
    }
}
