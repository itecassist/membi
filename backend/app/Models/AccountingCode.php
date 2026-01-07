<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingCode extends Model
{
    use HasFactory;
    protected $fillable = ['organisation_config_financial_id', 'code', 'description'];

    public function organisationConfigFinancial(): BelongsTo
    {
        return $this->belongsTo(OrganisationConfigFinancial::class);
    }
}
