<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum DeploymentMode: string
{
    case SharedSaas = 'shared_saas';
    case DedicatedSaas = 'dedicated_saas';
    case OnPremise = 'on_premise';
    case PrivateCloud = 'private_cloud';
    case SovereignSelfHosted = 'sovereign_self_hosted';
}