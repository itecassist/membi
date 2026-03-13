<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vat extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_config_financial_id',
        'name',
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
        ];
    }

    public function financialConfig(): BelongsTo
    {
        return $this->belongsTo(OrganisationConfigFinancial::class, 'organisation_config_financial_id');
    }
}
