<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationConfigMember extends Model
{
    use HasFactory;
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
        'has_group_members',
        'does_each_group_member_have_membership_number',
        'has_membership_numbers',
        'does_membership_numbers_auto_increment',
        'can_member_sign_declaration_for_other_adult_members',
        'prompt_admin_to_remove_inactive_members',
        'max_days_inactive',
    ];
}
