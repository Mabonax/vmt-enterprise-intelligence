<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum ProvisioningStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Provisioning = 'provisioning';
    case Active = 'active';
    case Suspended = 'suspended';
    case Retired = 'retired';
}