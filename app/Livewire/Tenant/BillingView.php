<?php

namespace App\Livewire\Tenant;

use App\Models\Bill;
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

        return view('livewire.tenant.billing-view', compact('bills'));
    }
}
