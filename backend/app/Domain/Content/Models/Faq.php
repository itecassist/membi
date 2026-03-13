<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faq extends Model
{
    use HasUuids;

    protected $fillable = [
        'faq_category_id',
        'question',
        'answer',
        'sort_order',
        'display_on_help',
        'paused',
    ];

    protected function casts(): array
    {
        return [
            'display_on_help' => 'boolean',
            'paused'          => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(FaqTag::class);
    }
}
