<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic lookup/reference data for an organisation.
 *
 * Note: organisation_id is stored as an integer (0 = platform-wide) per the migration.
 */
class Lookup extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'value',
    ];
}
