<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'currency_code',
        'currency_symbol',
        'symbol_left',
        'decimal_place',
        'decimal_point',
        'thousands_point',
    ];
}
