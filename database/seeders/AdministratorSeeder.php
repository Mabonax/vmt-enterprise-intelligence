<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Auth\SystemRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdministratorSeeder extends Seeder
{
    /**
     * Seed the foundational system roles and administrator account.
     */
    public function run(): void
    {
        foreach (SystemRole::values() as $role) {
            Role::findOrCreate($role, 'web');
        }

        $administrator = User::query()->firstOrCreate(
            ['email' => env('VIP_ADMIN_EMAIL', 'admin@vip.local')],
            [
                'name' => env('VIP_ADMIN_NAME', 'VIP Administrator'),
                'password' => Hash::make(env('VIP_ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ],
        );

        if (! $administrator->hasRole(SystemRole::Administrator->value)) {
            $administrator->assignRole(SystemRole::Administrator->value);
        }
    }
}
