<?php

namespace App\Livewire\Tenant;

use App\Mail\TenantRequestMail;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\InitialPayment;
use App\Models\NotificationLog;
use App\Models\TenantRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Bills — Citiescapes')]
class BillingView extends Component
{
    /**
     * Tenant escalation hook for bills in delinquent/eviction status —
     * creates a pre-filled Tenant Request (category: arrears appeal) so
     * the GM sees the bill context, period, and balance in one place,
     * and the tenant doesn't have to re-type it on the requests page.
     */
    public function requestPaymentPlan(int $billId): void
    {
        $bill = Bill::where('tenant_id', auth()->id())->findOrFail($billId);

        if (!in_array($bill->status, ['delinquent', 'eviction'], true)) {
            session()->flash('error', 'Payment plan requests are only available for bills in delinquent or eviction status.');
            return;
        }

        // Don't let a tenant spam the GM if a previous appeal for this bill
        // is still pending. One open request per bill is enough.
        $existing = TenantRequest::where('tenant_id', auth()->id())
            ->where('subject', 'like', "%Bill #{$bill->id}%")
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();
        if ($existing) {
            session()->flash('error', 'You already have an open payment-plan request for this bill. Please wait for the GM to respond.');
            return;
        }

        $subject = "Payment difficulty — Bill #{$bill->id} (" . ($bill->billing_period ?? 'initial fees') . ")";
        $body    = "I am unable to settle the full amount of ₱" . number_format($bill->total_amount, 2)
                 . " for bill #{$bill->id} (status: {$bill->status}). "
                 . "I would like to request a payment plan or other arrangement. "
                 . "Please contact me to discuss the options.\n\n— Auto-generated from billing portal —";

        $tr = TenantRequest::create([
            'tenant_id' => auth()->id(),
            'type'      => 'request',
            'subject'   => $subject,
            'body'      => $body,
        ]);

        AuditLog::record('payment_plan_requested', auth()->id(), 'tenant', 'SS3',
            "Tenant submitted payment-plan request for bill #{$bill->id}");

        // Notify all GMs.
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type'    => 'tenant_request',
                'source'  => 'SS7',
                'message' => 'Payment-plan request from ' . auth()->user()->full_name . ": {$subject}",
            ]);
            if ($gm->email) {
                try {
                    Mail::to($gm->email)->send(
                        new TenantRequestMail(auth()->user()->full_name, 'request', $subject, $body),
                    );
                } catch (\Throwable $e) {
                    \Log::error('Payment-plan TenantRequestMail send failed', ['gm_id' => $gm->id, 'error' => $e->getMessage()]);
                    // Bell notification still lands; email is best-effort.
                }
            }
        }

        session()->flash('success', 'Your payment-plan request has been sent to the General Manager. You will receive a reply via the in-app bell and email.');
    }

    public function render()
    {
        $bills = Bill::with(['payments', 'room'])
            ->where('tenant_id', auth()->id())
            ->latest()
            ->get();

        $initialPayment = InitialPayment::with('contract.room')
            ->where('tenant_id', auth()->id())
            ->latest()
            ->first();

        return view('livewire.tenant.billing-view', compact('bills', 'initialPayment'));
    }
}
