<?php

namespace App\Domain\Product\Models;

use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'organisation_id',
        'group_id',
        'name',
        'code',
        'description',
        'available',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductOptionVariant::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ProductOptionRule::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
