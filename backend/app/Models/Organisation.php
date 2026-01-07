<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'seo_name',
        'email',
        'phone',
        'logo',
        'website',
        'description',
        'is_active'
    ];
}
