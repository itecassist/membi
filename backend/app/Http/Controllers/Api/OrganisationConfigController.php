<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrganisationConfigFinancialResource;
use App\Http\Resources\OrganisationConfigMemberResource;
use App\Http\Resources\OrganisationConfigSubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationConfigController extends Controller
{
    // ── Member config ─────────────────────────────────────────────────────────

    public function showMember(Organisation $organisation): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);

        $config = $organisation->configMember()->firstOrCreate(['organisation_id' => $organisation->id]);

        return response()->json(['data' => new OrganisationConfigMemberResource($config)]);
    }

    public function updateMember(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);

        $validated = $request->validate([
            'should_authorize_members'                              => ['sometimes', 'boolean'],
            'require_2fa'                                           => ['sometimes', 'boolean'],
            'max_days_between_2fa'                                  => ['sometimes', 'integer', 'min:0'],
            'require_physical_address'                              => ['sometimes', 'boolean'],
            'require_physical_address_for_groups'                   => ['sometimes', 'boolean'],
            'has_junior_members'                                    => ['sometimes', 'boolean'],
            'junior_member_max_age'                                 => ['sometimes', 'integer', 'min:0', 'max:100'],
            'junior_member_auto_renew_to_adult'                     => ['sometimes', 'boolean'],
            'has_family_membership'                                 => ['sometimes', 'boolean'],
            'family_membership_max_adults'                          => ['sometimes', 'integer', 'min:1'],
            'family_membership_max_juniors'                         => ['sometimes', 'integer', 'min:0'],
            'has_group_members'                                     => ['sometimes', 'boolean'],
            'does_each_group_member_have_membership_number'         => ['sometimes', 'boolean'],
            'has_membership_numbers'                                => ['sometimes', 'boolean'],
            'does_membership_numbers_auto_increment'                => ['sometimes', 'boolean'],
            'can_member_sign_declaration_for_other_adult_members'   => ['sometimes', 'boolean'],
            'prompt_admin_to_remove_inactive_members'               => ['sometimes', 'boolean'],
            'max_days_inactive'                                     => ['sometimes', 'integer', 'min:0'],
        ]);

        $config = $organisation->configMember()->updateOrCreate(
            ['organisation_id' => $organisation->id],
            $validated
        );

        return response()->json(['data' => new OrganisationConfigMemberResource($config)]);
    }

    // ── Subscription config ───────────────────────────────────────────────────

    public function showSubscription(Organisation $organisation): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);

        $config = $organisation->configSubscription()->firstOrCreate(['organisation_id' => $organisation->id]);

        return response()->json(['data' => new OrganisationConfigSubscriptionResource($config)]);
    }

    public function updateSubscription(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);

        $validated = $request->validate([
            'can_member_have_more_than_one_subscription'  => ['sometimes', 'boolean'],
            'can_have_subscription_without_membership'    => ['sometimes', 'boolean'],
            'recently_expired_annual_subscription_months' => ['sometimes', 'integer', 'min:0'],
            'recently_expired_monthly_subscription_days'  => ['sometimes', 'integer', 'min:0'],
            'recently_expired_other_period_days'          => ['sometimes', 'integer', 'min:0'],
            'renew_annual_subscription_months'            => ['sometimes', 'integer', 'min:0'],
            'renew_monthly_subscription_days'             => ['sometimes', 'integer', 'min:0'],
            'renew_other_subscription_days'               => ['sometimes', 'integer', 'min:0'],
            'forced_joining_fee'                          => ['sometimes', 'boolean'],
            'subscription_joining_id'                     => ['nullable', 'integer'],
            'auto_renewal_order_days'                     => ['sometimes', 'integer', 'min:1'],
        ]);

        $config = $organisation->configSubscription()->updateOrCreate(
            ['organisation_id' => $organisation->id],
            $validated
        );

        return response()->json(['data' => new OrganisationConfigSubscriptionResource($config)]);
    }

    // ── Financial config ──────────────────────────────────────────────────────

    public function showFinancial(Organisation $organisation): JsonResponse
    {
        abort_unless(request()->member->can('organisation.config.update'), 403);

        $config = $organisation->configFinancial()->firstOrCreate(['organisation_id' => $organisation->id]);

        return response()->json(['data' => new OrganisationConfigFinancialResource($config)]);
    }

    public function updateFinancial(Request $request, Organisation $organisation): JsonResponse
    {
        abort_unless($request->member->can('organisation.config.update'), 403);

        $validated = $request->validate([
            'currency'           => ['sometimes', 'string', 'max:1'],
            'vat_status'         => ['sometimes', 'boolean'],
            'vat_number'         => ['sometimes', 'string', 'max:255'],
            'financial_year_end' => ['sometimes', 'string', 'max:10'],
        ]);

        $config = $organisation->configFinancial()->updateOrCreate(
            ['organisation_id' => $organisation->id],
            $validated
        );

        return response()->json(['data' => new OrganisationConfigFinancialResource($config)]);
    }
}
