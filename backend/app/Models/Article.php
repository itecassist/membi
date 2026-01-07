<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
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
}
