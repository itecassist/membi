<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    use HasFactory;

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

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
