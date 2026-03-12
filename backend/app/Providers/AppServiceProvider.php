<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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
