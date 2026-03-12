<?php

namespace App\Domain\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'organisation_id',
        'name',
        'email',
        'payment_method_id',
        'payment_reference',
        'status',
        'date_placed',
        'date_finished',
        'comments',
        'currency_code',
        'tax_total',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'date_placed'  => 'date',
            'date_finished' => 'date',
            'tax_total'    => 'decimal:2',
            'total'        => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Member\Models\Member::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organisation\Models\Organisation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
