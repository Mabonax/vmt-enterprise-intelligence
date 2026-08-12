<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum PackageTier: string
{
    case StarterIntelligence = 'starter_intelligence';
    case ProfessionalIntelligence = 'professional_intelligence';
    case EnterpriseIntelligence = 'enterprise_intelligence';
    case SovereignIntelligence = 'sovereign_intelligence';
    case GovernmentNgoIntelligence = 'government_ngo_intelligence';
    case HealthcareIntelligence = 'healthcare_intelligence';
    case EducationIntelligence = 'education_intelligence';
    case CustomManagedIntelligence = 'custom_managed_intelligence';
}