<?php

namespace App\Livewire\Tenant;

use App\Models\Bill;
use App\Models\InitialPayment;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Bills — Citiescapes')]
class BillingView extends Component
{
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
