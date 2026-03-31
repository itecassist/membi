<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Member\Models\Group;
use App\Domain\Member\Models\Member;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInstanceAllocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'member_subscription_id',
        'member_id',
        'group_id',
        'allocated_at',
    ];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'datetime',
        ];
    }

    public function memberSubscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
