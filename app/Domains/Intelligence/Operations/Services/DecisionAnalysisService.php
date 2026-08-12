<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionDecisionRecord;

class DecisionAnalysisService
{
    public function record(EnterpriseMission $mission, array $decision): MissionDecisionRecord
    {
        return MissionDecisionRecord::query()->create([
            'enterprise_mission_id' => $mission->id,
            'decision_key' => $decision['decision_key'] ?? 'strategic-decision',
            'context' => $decision['context'] ?? ['mission' => $mission->title],
            'alternatives' => $decision['alternatives'] ?? [],
            'reasoning' => $decision['reasoning'] ?? 'Decision recorded by executive orchestration layer.',
            'evidence' => $decision['evidence'] ?? [],
            'confidence_score' => $decision['confidence_score'] ?? 80,
            'chosen_option' => $decision['chosen_option'] ?? null,
            'approvals' => $decision['approvals'] ?? [],
            'outcome' => $decision['outcome'] ?? null,
            'lessons_learned' => $decision['lessons_learned'] ?? null,
            'review_at' => $decision['review_at'] ?? now()->addMonth(),
        ]);
    }
}
