<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationList extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'query',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
