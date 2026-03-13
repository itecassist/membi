<?php

namespace App\Domain\Organisation\Models;

use App\Domain\Member\Models\Member;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records which member holds an admin role for an organisation.
 *
 * Note: role_id references the Spatie `roles` table using an integer FK
 * (not UUID) — this matches the migration definition.
 */
class OrganisationConfigAdmin extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'member_id',
        'role_id',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
