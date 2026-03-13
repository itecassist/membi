<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'title',
        'article_category_id',
        'page_title',
        'seo_name',
        'content',
        'summary',
        'seo_description',
        'featured',
        'live',
        'category_live',
        'popularity',
    ];

    protected function casts(): array
    {
        return [
            'featured'      => 'boolean',
            'live'          => 'boolean',
            'category_live' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ArticleTag::class);
    }
}
