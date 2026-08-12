<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\SubscriptionInvoice;
use App\Domains\Intelligence\Commercial\Models\SubscriptionInvoiceLine;
use Illuminate\Support\Str;

class InvoiceGenerationService
{
    public function generate(IntelligenceSubscription $subscription): SubscriptionInvoice
    {
        $basePrice = (float) $subscription->monthly_price;
        $overageTotal = (float) $subscription->overages()->sum('quantity') * 0.5;

        $invoice = SubscriptionInvoice::query()->create([
            'intelligence_subscription_id' => $subscription->id,
            'billing_account_id' => $subscription->billing_account_id,
            'invoice_number' => 'INV-'.strtoupper(Str::random(8)),
            'status' => 'draft',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'subtotal' => $basePrice + $overageTotal,
            'tax_total' => round(($basePrice + $overageTotal) * 0.15, 2),
            'grand_total' => round(($basePrice + $overageTotal) * 1.15, 2),
            'currency' => $subscription->currency,
            'payload' => [
                'package' => $subscription->package?->name,
                'overage_count' => $subscription->overages()->count(),
            ],
        ]);

        SubscriptionInvoiceLine::query()->create([
            'subscription_invoice_id' => $invoice->id,
            'line_type' => 'subscription',
            'description' => $subscription->package?->name ?? 'Commercial subscription',
            'quantity' => 1,
            'unit_price' => $basePrice,
            'line_total' => $basePrice,
            'metadata' => [],
        ]);

        if ($overageTotal > 0) {
            SubscriptionInvoiceLine::query()->create([
                'subscription_invoice_id' => $invoice->id,
                'line_type' => 'overage',
                'description' => 'Metered usage overage',
                'quantity' => 1,
                'unit_price' => $overageTotal,
                'line_total' => $overageTotal,
                'metadata' => [],
            ]);
        }

        return $invoice->fresh('lines');
    }
}