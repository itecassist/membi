<?php

namespace App\Domain\Checkout\Models;

use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'basket_id',
        'organisation_id',
        'step',
        'mode',
        'initiator_email',
        'allocations',
        'forms',
        'order_id',
        'payment_status',
        'area_status',
    ];

    protected function casts(): array
    {
        return [
            'allocations' => 'array',
            'forms'       => 'array',
            'area_status' => 'array',
        ];
    }

    public function basket(): BelongsTo
    {
        return $this->belongsTo(Basket::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function isComplete(): bool
    {
        return $this->step === 'complete';
    }

    public function markStepComplete(string $step): void
    {
        $status = $this->area_status ?? [];
        $status[$step] = 'complete';
        $this->update(['area_status' => $status]);
    }
}
