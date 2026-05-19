<?php

namespace App\Livewire\Tenant;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\NotificationLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Dashboard — Citiescapes')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $contract = Contract::with('room')->where('tenant_id', $user->id)->active()->first();
        $latestBill = Bill::with(['room', 'payments'])
            ->where('tenant_id', $user->id)->latest()->first();
        $unpaidCount = Bill::where('tenant_id', $user->id)->unpaid()->count();
        $notifications = NotificationLog::where('user_id', $user->id)->latest()->take(5)->get();

        // Initial payment (move-in collection) — shown on dashboard
        $initialPayment = InitialPayment::with('contract.room')
            ->where('tenant_id', $user->id)->first();

        return view('livewire.tenant.dashboard', compact(
            'user',
            'contract',
            'latestBill',
            'unpaidCount',
            'notifications',
            'initialPayment'
        ));
    }
}
