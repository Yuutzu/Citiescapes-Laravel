<?php

namespace App\Livewire\Admin;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\Inquiry;
use App\Models\Room;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard — Citiescapes')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalRooms'      => Room::count(),
            'availableRooms'  => Room::available()->count(),
            'occupiedRooms'   => Room::occupied()->count(),
            'maintenanceRooms'=> Room::underMaintenance()->count(),
            'activeTenants'   => User::where('role', 'tenant')->where('status', 'active')->count(),
            'activeContracts' => Contract::active()->count(),
            'expiringContracts'=> Contract::expiring(30)->count(),
            'pendingInquiries'=> Inquiry::pending()->count(),
            'unpaidBills'     => Bill::unpaid()->count(),
            'overdueCount'    => Bill::whereIn('status', ['overdue','delinquent','eviction'])->count(),
            'recentInquiries' => Inquiry::latest()->take(5)->get(),
            'expiringList'    => Contract::expiring(30)->with('tenant','room')->take(5)->get(),
            'rooms'           => Room::with('currentTenant')->orderBy('floor_level')->orderBy('room_number')->get(),
        ]);
    }
}
