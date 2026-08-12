<?php

namespace App\Providers;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleDashboard;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleHealthSnapshot;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleWidget;
use App\Domains\Intelligence\AdminConsole\Policies\AdminConsolePolicy;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;
use App\Domains\Intelligence\Commercial\Policies\CommercialAccessPolicy;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Policies\GatewayClientPolicy;
use App\Domains\Intelligence\Security\Policies\GatewayTenantPolicy;
use App\Domains\Intelligence\Models\Agent;
use App\Policies\AgentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Agent::class, AgentPolicy::class);
        Gate::policy(AdminConsoleDashboard::class, AdminConsolePolicy::class);
        Gate::policy(AdminConsoleWidget::class, AdminConsolePolicy::class);
        Gate::policy(AdminConsoleAlert::class, AdminConsolePolicy::class);
        Gate::policy(AdminConsoleAction::class, AdminConsolePolicy::class);
        Gate::policy(AdminConsoleAuditEvent::class, AdminConsolePolicy::class);
        Gate::policy(AdminConsoleHealthSnapshot::class, AdminConsolePolicy::class);
        Gate::policy(IntelligencePackage::class, CommercialAccessPolicy::class);
        Gate::policy(IntelligenceTenant::class, CommercialAccessPolicy::class);
        Gate::policy(IntelligenceSubscription::class, CommercialAccessPolicy::class);
        Gate::policy(IntelligenceProposal::class, CommercialAccessPolicy::class);
        Gate::policy(SupportTicket::class, CommercialAccessPolicy::class);
        Gate::policy(GatewayClient::class, GatewayClientPolicy::class);
        Gate::policy(GatewayTenant::class, GatewayTenantPolicy::class);
        Vite::prefetch(concurrency: 3);
    }
}
