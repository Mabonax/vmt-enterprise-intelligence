<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Auth\SystemRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class AdministratorSeeder extends Seeder
{
    /**
     * Seed roles and an administrator without allowing demo credentials in production.
     */
    public function run(): void
    {
        foreach (SystemRole::values() as $role) {
            Role::findOrCreate($role, 'web');
        }

        $email = (string) env('VIP_ADMIN_EMAIL', 'admin@vip.local');
        $password = (string) env('VIP_ADMIN_PASSWORD', 'password');

        if (app()->environment('production') && (
            $email === 'admin@vip.local'
            || $password === 'password'
            || trim($password) === ''
        )) {
            throw new RuntimeException('Production administrator credentials must be configured securely before seeding.');
        }

        $administrator = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => env('VIP_ADMIN_NAME', 'VIP Administrator'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        if (! $administrator->hasRole(SystemRole::Administrator->value)) {
            $administrator->assignRole(SystemRole::Administrator->value);
        }
    }
}
