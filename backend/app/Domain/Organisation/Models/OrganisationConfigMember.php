<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationConfigMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'should_authorize_members',
        'require_2fa',
        'max_days_between_2fa',
        'require_physical_address',
        'require_physical_address_for_groups',
        'has_junior_members',
        'junior_member_max_age',
        'junior_member_auto_renew_to_adult',
        'has_family_membership',
        'family_membership_max_adults',
        'family_membership_max_juniors',
        'has_group_members',
        'does_each_group_member_have_membership_number',
        'has_membership_numbers',
        'does_membership_numbers_auto_increment',
        'can_member_sign_declaration_for_other_adult_members',
        'prompt_admin_to_remove_inactive_members',
        'max_days_inactive',
    ];

    protected function casts(): array
    {
        return [
            'should_authorize_members'                         => 'boolean',
            'require_2fa'                                      => 'boolean',
            'require_physical_address'                         => 'boolean',
            'require_physical_address_for_groups'              => 'boolean',
            'has_junior_members'                               => 'boolean',
            'junior_member_auto_renew_to_adult'                => 'boolean',
            'has_family_membership'                            => 'boolean',
            'has_group_members'                                => 'boolean',
            'does_each_group_member_have_membership_number'    => 'boolean',
            'has_membership_numbers'                           => 'boolean',
            'does_membership_numbers_auto_increment'           => 'boolean',
            'can_member_sign_declaration_for_other_adult_members' => 'boolean',
            'prompt_admin_to_remove_inactive_members'          => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
