<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Commercial\DTOs\UsageMeterRecordData;
use App\Domains\Intelligence\Commercial\Models\BillingAccount;
use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ReleaseReadinessCheck;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;
use App\Domains\Intelligence\Commercial\Models\TenantProvisioningRequest;
use App\Domains\Intelligence\Commercial\Services\BillingReadinessService;
use App\Domains\Intelligence\Commercial\Services\CommercialHandoverService;
use App\Domains\Intelligence\Commercial\Services\DeploymentRunbookService;
use App\Domains\Intelligence\Commercial\Services\IntelligenceProposalBuilder;
use App\Domains\Intelligence\Commercial\Services\InvoiceGenerationService;
use App\Domains\Intelligence\Commercial\Services\ProposalApprovalService;
use App\Domains\Intelligence\Commercial\Services\ReleaseReadinessService;
use App\Domains\Intelligence\Commercial\Services\SlaMonitor;
use App\Domains\Intelligence\Commercial\Services\SubscriptionService;
use App\Domains\Intelligence\Commercial\Services\SupportTicketService;
use App\Domains\Intelligence\Commercial\Services\TenantProvisioningService;
use App\Domains\Intelligence\Commercial\Services\UsageMeteringService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommercialController extends Controller
{
    public function __construct(
        private readonly TenantProvisioningService $provisioning,
        private readonly SubscriptionService $subscriptions,
        private readonly UsageMeteringService $metering,
        private readonly InvoiceGenerationService $invoices,
        private readonly BillingReadinessService $billingReadiness,
        private readonly IntelligenceProposalBuilder $proposalBuilder,
        private readonly ProposalApprovalService $proposalApprovals,
        private readonly CommercialHandoverService $handovers,
        private readonly DeploymentRunbookService $runbooks,
        private readonly ReleaseReadinessService $releaseReadiness,
        private readonly SupportTicketService $support,
        private readonly SlaMonitor $slaMonitor,
    ) {}

    public function packages(): JsonResponse
    {
        return response()->json([
            'packages' => IntelligencePackage::query()
                ->with(['features', 'limits', 'entitlements', 'moduleAccesses', 'pricingRules', 'upgradePaths'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function tenants(): JsonResponse
    {
        return response()->json([
            'tenants' => IntelligenceTenant::query()
                ->with(['workspaces', 'domains', 'brandingProfile', 'securityProfile', 'deploymentProfile'])
                ->latest()
                ->get(),
        ]);
    }

    public function provisioning(): JsonResponse
    {
        return response()->json([
            'queue' => TenantProvisioningRequest::query()->with(['tenant', 'package', 'checklist'])->latest()->get(),
        ]);
    }

    public function submitProvisioning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
            'package_id' => ['required', 'string'],
            'deployment_mode' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenant = IntelligenceTenant::query()->findOrFail($validated['tenant_id']);
        $package = IntelligencePackage::query()->findOrFail($validated['package_id']);

        return response()->json([
            'request' => $this->provisioning->submit($request->user(), $tenant, $package, $validated),
        ], 201);
    }

    public function activateProvisioning(TenantProvisioningRequest $provisioningRequest): JsonResponse
    {
        return response()->json([
            'provisioning' => $this->provisioning->approve($provisioningRequest)->toArray(),
        ]);
    }

    public function subscriptions(): JsonResponse
    {
        return response()->json([
            'subscriptions' => IntelligenceSubscription::query()->with(['tenant', 'package', 'meters', 'quotas', 'overages'])->latest()->get(),
        ]);
    }

    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
            'package_id' => ['required', 'string'],
        ]);

        $tenant = IntelligenceTenant::query()->findOrFail($validated['tenant_id']);
        $package = IntelligencePackage::query()->findOrFail($validated['package_id']);

        return response()->json([
            'subscription' => $this->subscriptions->create($tenant, $package),
        ], 201);
    }

    public function usage(): JsonResponse
    {
        return response()->json([
            'usage' => IntelligenceSubscription::query()->with(['meters.ledgerEntries', 'quotas', 'overages'])->latest()->get(),
        ]);
    }

    public function recordUsage(Request $request, IntelligenceSubscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'meter_key' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'context' => ['nullable', 'array'],
        ]);

        return response()->json([
            'usage' => $this->metering->record(
                $subscription->id,
                new UsageMeterRecordData(
                    meterKey: $validated['meter_key'],
                    quantity: (float) $validated['quantity'],
                    context: $validated['context'] ?? [],
                ),
            ),
        ]);
    }

    public function billing(): JsonResponse
    {
        return response()->json([
            'billing_accounts' => BillingAccount::query()->with(['contacts', 'paymentInstructions'])->latest()->get(),
            'readiness' => $this->billingReadiness->summary(),
        ]);
    }

    public function generateInvoice(IntelligenceSubscription $subscription): JsonResponse
    {
        return response()->json([
            'invoice' => $this->invoices->generate($subscription),
        ]);
    }

    public function proposals(): JsonResponse
    {
        return response()->json([
            'proposals' => IntelligenceProposal::query()
                ->with(['tenant', 'package', 'packageLines', 'deploymentLines', 'serviceLines', 'assumptions', 'risks', 'approvals'])
                ->latest()
                ->get(),
        ]);
    }

    public function createProposal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
            'package_id' => ['required', 'string'],
            'deployment_mode' => ['required', 'string', 'max:64'],
        ]);

        $tenant = IntelligenceTenant::query()->findOrFail($validated['tenant_id']);
        $package = IntelligencePackage::query()->findOrFail($validated['package_id']);

        return response()->json([
            'proposal' => $this->proposalBuilder->build($tenant, $package, $validated['deployment_mode']),
        ], 201);
    }

    public function moveProposal(Request $request, IntelligenceProposal $proposal): JsonResponse
    {
        $validated = $request->validate(['stage' => ['required', 'string', 'max:64']]);

        return response()->json([
            'proposal' => $this->proposalApprovals->moveToStage($proposal, $validated['stage']),
        ]);
    }

    public function deployments(): JsonResponse
    {
        return response()->json([
            'runbooks' => DeploymentRunbook::query()->with('steps')->latest()->get(),
        ]);
    }

    public function createRunbook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
            'proposal_id' => ['nullable', 'string'],
        ]);

        $tenant = IntelligenceTenant::query()->findOrFail($validated['tenant_id']);

        return response()->json([
            'runbook' => $this->runbooks->create($tenant, $validated['proposal_id'] ?? null),
        ], 201);
    }

    public function support(): JsonResponse
    {
        return response()->json([
            'tickets' => SupportTicket::query()->latest()->get(),
            'sla' => $this->slaMonitor->summary(),
        ]);
    }

    public function createSupportTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:64'],
        ]);

        $tenant = IntelligenceTenant::query()->findOrFail($validated['tenant_id']);

        return response()->json([
            'ticket' => $this->support->open($tenant, $validated['title'], $validated['severity'] ?? 'medium'),
        ], 201);
    }

    public function moveSupportTicket(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', 'string', 'max:64']]);

        return response()->json([
            'ticket' => $this->support->advance($supportTicket, $validated['status']),
        ]);
    }

    public function readiness(): JsonResponse
    {
        return response()->json([
            'checks' => ReleaseReadinessCheck::query()->latest()->get(),
        ]);
    }

    public function runReadiness(IntelligenceTenant $tenant): JsonResponse
    {
        return response()->json([
            'check' => $this->releaseReadiness->check($tenant),
        ]);
    }

    public function handover(IntelligenceProposal $proposal): JsonResponse
    {
        return response()->json([
            'handover' => $this->handovers->handover($proposal),
        ]);
    }
}