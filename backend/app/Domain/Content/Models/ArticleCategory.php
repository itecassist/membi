<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'seo_name',
        'description',
        'live',
        'article_live',
        'section_id',
        'tree_left',
        'tree_right',
        'tree_level',
    ];

    protected function casts(): array
    {
        return [
            'live'         => 'boolean',
            'article_live' => 'boolean',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
