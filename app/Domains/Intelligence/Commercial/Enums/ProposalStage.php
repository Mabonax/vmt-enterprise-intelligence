<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum ProposalStage: string
{
    case Draft = 'draft';
    case InternalReview = 'internal_review';
    case Approved = 'approved';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Converted = 'converted';
}