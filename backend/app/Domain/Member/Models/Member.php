<?php

namespace App\Domain\Member\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Member extends Model
{
    use HasFactory, HasUuids, HasRoles, SoftDeletes;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'user_id',
        'organisation_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'mobile_phone',
        'date_of_birth',
        'gender',
        'member_number',
        'classification',
        'joined_at',
        'is_active',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'joined_at'    => 'date',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organisation\Models\Organisation::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Domain\Subscription\Models\MemberSubscription::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->title ? $this->title . ' ' : '') . $this->first_name . ' ' . $this->last_name);
    }
}
