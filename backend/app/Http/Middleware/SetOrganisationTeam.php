<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets Spatie's team context from the {organisation} route parameter so that
 * all role/permission lookups in downstream controllers are automatically
 * scoped to the correct organisation. Must run after route model binding.
 */
class SetOrganisationTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $organisation = $request->route('organisation');

        if ($organisation) {
            setPermissionsTeamId($organisation->id);
        }

        return $next($request);
    }
}
