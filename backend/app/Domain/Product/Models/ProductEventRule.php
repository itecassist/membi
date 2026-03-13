<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductEventRule extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'visible_to_non_members',
        'published',
        'members_only',
        'renewable',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'visible_to_non_members' => 'boolean',
            'published'              => 'boolean',
            'start_date'             => 'datetime',
            'end_date'               => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
