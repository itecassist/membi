<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets Spatie's team context so that all role/permission lookups are scoped
 * to the correct organisation. Resolves from:
 *   1. {orgSlug} route param (path-based routing — primary path)
 *   2. {organisation} route param (direct model binding — admin routes)
 */
class SetOrganisationTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $organisation = $request->route('orgSlug') ?? $request->route('organisation');

        if ($organisation) {
            setPermissionsTeamId($organisation->id);

            // Controllers are typed `Organisation $organisation`. When routing via
            // orgSlug there is no {organisation} path segment, so we inject it
            // as a route parameter here so all controllers continue to work unchanged.
            if ($request->route('orgSlug') && ! $request->route('organisation')) {
                $request->route()->setParameter('organisation', $organisation);
            }
        }

        return $next($request);
    }
}
