<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationConfigFinancial extends Model
{
    use HasFactory;
    protected $fillable = [
        'organisation_id',
        'currency',
        'vat_status',
        'vat_number',
        'financial_year_end',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
