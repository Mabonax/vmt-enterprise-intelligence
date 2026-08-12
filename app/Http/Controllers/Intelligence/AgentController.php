<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Services\AgentManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intelligence\AgentUpsertRequest;
use Illuminate\Http\RedirectResponse;

class AgentController extends Controller
{
    public function __construct(
        private readonly AgentManager $agents,
    ) {}

    public function store(AgentUpsertRequest $request): RedirectResponse
    {
        $this->authorize('create', Agent::class);

        $this->agents->create(array_merge(
            $request->validated(),
            [
                'owner_user_id' => $request->user()?->id,
                'organization_id' => $request->user()?->organization_id,
            ],
        ));

        return back()->with('success', 'Agent created.');
    }

    public function update(AgentUpsertRequest $request, Agent $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $this->agents->update($agent, $request->validated());

        return back()->with('success', 'Agent updated.');
    }
}
