<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationConfigMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                                                    => $this->id,
            'organisation_id'                                       => $this->organisation_id,
            'should_authorize_members'                              => $this->should_authorize_members,
            'require_2fa'                                           => $this->require_2fa,
            'max_days_between_2fa'                                  => $this->max_days_between_2fa,
            'require_physical_address'                              => $this->require_physical_address,
            'require_physical_address_for_groups'                   => $this->require_physical_address_for_groups,
            'has_junior_members'                                    => $this->has_junior_members,
            'junior_member_max_age'                                 => $this->junior_member_max_age,
            'junior_member_auto_renew_to_adult'                     => $this->junior_member_auto_renew_to_adult,
            'has_family_membership'                                 => $this->has_family_membership,
            'family_membership_max_adults'                          => $this->family_membership_max_adults,
            'family_membership_max_juniors'                         => $this->family_membership_max_juniors,
            'has_group_members'                                     => $this->has_group_members,
            'does_each_group_member_have_membership_number'         => $this->does_each_group_member_have_membership_number,
            'has_membership_numbers'                                => $this->has_membership_numbers,
            'does_membership_numbers_auto_increment'                => $this->does_membership_numbers_auto_increment,
            'can_member_sign_declaration_for_other_adult_members'   => $this->can_member_sign_declaration_for_other_adult_members,
            'prompt_admin_to_remove_inactive_members'               => $this->prompt_admin_to_remove_inactive_members,
            'max_days_inactive'                                     => $this->max_days_inactive,
            'updated_at'                                            => $this->updated_at,
        ];
    }
}
