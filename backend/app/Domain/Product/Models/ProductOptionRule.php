<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_option_id',
        'model',
        'field',
        'operator',
        'value',
        'action_option_id',
        'auto',
    ];

    protected function casts(): array
    {
        return [
            'auto' => 'boolean',
        ];
    }

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }
}
