<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'model',
        'field',
        'operator',
        'value',
        'action_option_id',
        'auto',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }
}
