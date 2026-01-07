<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqTag extends Model
{
    use HasFactory;
    protected $fillable = [
        'faq_category_id',
        'question',
        'answer',
        'sort_order',
        'display_on_help',
        'paused',
    ];
}
