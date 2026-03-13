<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'description',
        'sort_order',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }
}
