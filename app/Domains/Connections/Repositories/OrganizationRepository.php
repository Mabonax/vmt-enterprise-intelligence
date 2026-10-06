<?php

declare(strict_types=1);

namespace App\Domains\Connections\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationRepository
{
    public function create(array $data): string
    {
        $id = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $id, 'name' => $data['name'], 'code' => $data['code'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
            'status' => 'active', 'settings' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $id;
    }
}
