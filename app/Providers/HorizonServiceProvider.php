<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null): bool {
            if (! $user) {
                return false;
            }

            $allowedEmails = array_values(array_filter(array_map(
                static fn (string $email): string => trim($email),
                explode(',', (string) env('HORIZON_ALLOWED_EMAILS', ''))
            )));

            if ($allowedEmails !== [] && in_array($user->email, $allowedEmails, true)) {
                return true;
            }

            $allowedRoles = array_values(array_filter(array_map(
                static fn (string $role): string => trim($role),
                explode(',', (string) env('HORIZON_ALLOWED_ROLES', 'super-admin'))
            )));

            if ($allowedRoles !== [] && method_exists($user, 'hasRole') && $user->hasRole(...$allowedRoles)) {
                return true;
            }

            $permission = (string) env('HORIZON_ALLOWED_PERMISSION', 'horizon.view');

            if ($permission !== '' && method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
                return true;
            }

            return false;
        });
    }
}
