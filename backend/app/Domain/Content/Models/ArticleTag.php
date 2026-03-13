<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTag extends Model
{
    use HasUuids;

    protected $fillable = [
        'article_id',
        'tag',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
