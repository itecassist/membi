<?php

namespace App\Domain\Member\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'name',
        'type',
        'email_contact_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organisation\Models\Organisation::class);
    }

    public function emailContact(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'email_contact_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'group_members')
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'group_members')
            ->wherePivot('is_admin', true)
            ->withTimestamps();
    }
}
