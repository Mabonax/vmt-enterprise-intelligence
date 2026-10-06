<?php

declare(strict_types=1);

namespace App\Domains\Connections\Services;

use App\Domains\Connections\Repositories\OrganizationRepository;
use Illuminate\Support\Facades\DB;

class OrganizationRegistrationService
{
    public function __construct(private readonly OrganizationRepository $organizations) {}

    public function register(array $data): string
    {
        return DB::transaction(fn () => $this->organizations->create($data));
    }
}
