<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentQualityScore extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'completeness_score', 'evidence_score', 'policy_score', 'verification_score', 'overall_score', 'metadata'];

    protected function casts(): array
    {
        return ['completeness_score' => 'float', 'evidence_score' => 'float', 'policy_score' => 'float', 'verification_score' => 'float', 'overall_score' => 'float', 'metadata' => 'array'];
    }
}
