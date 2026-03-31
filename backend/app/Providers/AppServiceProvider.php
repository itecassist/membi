<?php

namespace App\Providers;

use App\Domain\Organisation\Models\Organisation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Resolve the {subdomain} route parameter to an Organisation model by seo_name.
        // This allows Route::domain('{subdomain}.example.com') to automatically inject
        // the Organisation without needing an {organisation} path segment in the URL.
        Route::bind('subdomain', function (string $value) {
            return Organisation::where('seo_name', $value)
                ->where('is_active', true)
                ->firstOrFail();
        });

        // super-admin members bypass all permission checks within their organisation.
        // Spatie calls Gate::before before any permission check.
        Gate::before(function ($user, $ability) {
            // $user here is a Member model (when permission is checked on a member)
            if ($user instanceof \App\Domain\Member\Models\Member) {
                return $user->hasRole('super-admin') ? true : null;
            }
        });
    }
}
