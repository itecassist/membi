<?php

namespace App\Http\Middleware;

use App\Domain\Member\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user's Member record for the current organisation
 * and attaches it to the request as $request->member.
 *
 * This allows controllers to check permissions on the correct scoped model:
 *   $request->member->can('member.update')
 *
 * Returns 403 if the user is not a member of the organisation.
 * Must run after auth:sanctum and SetOrganisationTeam.
 */
class ResolveMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $organisation = $request->route('organisation');

        if (! $organisation) {
            return $next($request);
        }

        $member = Member::where('user_id', $request->user()->id)
            ->where('organisation_id', $organisation->id)
            ->where('is_active', true)
            ->first();

        if (! $member) {
            return response()->json(['message' => 'You are not a member of this organisation.'], 403);
        }

        $request->merge(['_resolved_member' => $member]);
        $request->setUserResolver(fn () => $request->user());

        // Make it accessible as $request->member
        $request->member = $member;

        return $next($request);
    }
}
