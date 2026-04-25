<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPanelPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserPanelPermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $usersWithoutAppPanel = User::whereDoesntHave('panelPermissions', function ($query) {
            $query->where('panel', 'app');
        })->get();

        foreach ($usersWithoutAppPanel as $user) {
            if (! $user->tenant_id) {
                continue; // Skip users without tenant_id
            }
            UserPanelPermission::create([
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'panel' => 'app',
            ]);

            UserPanelPermission::clearCache($user->id);
        }

        $this->command->info("Added 'app' panel to {$usersWithoutAppPanel->count()} users.");
    }
}
