<?php

namespace App\Livewire\Admin;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard — Citiescapes')]
class Dashboard extends Component
{
    public function render()
    {
        // Last 6 months revenue from confirmed payments
        $revenueData = Payment::select(
                DB::raw("DATE_FORMAT(confirmed_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Fill in all 6 months including months with no payments
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[$key] = $revenueData[$key] ?? 0;
        }

        // Bill status breakdown for doughnut chart
        $billStatusCounts = Bill::select('status', DB::raw('count(*) as count'))
            ->whereNotIn('status', ['archived'])
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('livewire.admin.dashboard', [
            'totalRooms'        => Room::count(),
            'availableRooms'    => Room::available()->count(),
            'occupiedRooms'     => Contract::active()->count(),
            'maintenanceRooms'  => Room::underMaintenance()->count(),
            'activeTenants'     => User::where('role', 'tenant')->where('status', 'active')->count(),
            'activeContracts'   => Contract::active()->count(),
            'expiringContracts' => Contract::expiring(30)->count(),
            'pendingInquiries'  => Inquiry::pending()->count(),
            'unpaidBills'       => Bill::unpaid()->count(),
            'overdueCount'      => Bill::whereIn('status', ['overdue', 'delinquent', 'eviction'])->count(),
            'totalRevenue'      => Payment::whereNotNull('confirmed_at')->sum('amount'),
            'recentInquiries'   => Inquiry::latest()->take(5)->get(),
            'expiringList'      => Contract::expiring(30)->with('tenant', 'room')->take(5)->get(),
            'recentInitialPayments' => InitialPayment::with('tenant', 'contract.room')
                ->latest('date_received')->take(5)->get(),
            'totalInitialCollected' => InitialPayment::sum('total_collected'),
            'rooms'             => Room::with('currentTenant')->orderBy('floor_level')->orderBy('room_number')->get(),
            'revenueLabels'     => $months->keys()->map(fn($m) => \Carbon\Carbon::parse($m)->format('M Y'))->values(),
            'revenueValues'     => $months->values(),
            'billStatusCounts'  => $billStatusCounts,
        ]);
    }
}
