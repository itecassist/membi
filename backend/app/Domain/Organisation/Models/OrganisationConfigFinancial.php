<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganisationConfigFinancial extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'currency',
        'vat_status',
        'vat_number',
        'financial_year_end',
    ];

    protected function casts(): array
    {
        return [
            'vat_status' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function accountingCodes(): HasMany
    {
        return $this->hasMany(AccountingCode::class, 'organisation_config_financial_id');
    }

    public function vats(): HasMany
    {
        return $this->hasMany(Vat::class, 'organisation_config_financial_id');
    }
}
