<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'emailable_id',
        'emailable_type',
        'subject',
        'eml',
        'from',
        'to',
    ];
}
