<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\SemanticMemory;
use App\Models\User;
use Illuminate\Support\Collection;

class MemoryRetriever
{
    public function retrieveForUser(User $user, ?Agent $agent = null, int $limit = 8): Collection
    {
        return SemanticMemory::query()
            ->where(function ($query) use ($user, $agent): void {
                $query->where(function ($private) use ($user): void {
                    $private->where('subject_type', User::class)
                        ->where('subject_id', (string) $user->id);
                })->orWhere('visibility', 'global')
                    ->orWhere(function ($organization) use ($user): void {
                        if ($user->organization_id !== null) {
                            $organization->where('visibility', 'organization')
                                ->where('organization_id', $user->organization_id);
                        }
                    });

                if ($agent !== null) {
                    $query->orWhere('agent_id', $agent->id);
                }
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('importance_score')
            ->orderByDesc('confidence_score')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
