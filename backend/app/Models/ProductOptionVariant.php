<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'lookup_id',
        'deductible',
        'amount',
    ];

    protected $casts = [
        'deductible' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function lookup(): BelongsTo
    {
        return $this->belongsTo(Lookup::class);
    }
}
