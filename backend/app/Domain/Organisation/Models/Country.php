<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasUuids;

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

    protected function casts(): array
    {
        return [
            'symbol_left' => 'boolean',
        ];
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }
}
