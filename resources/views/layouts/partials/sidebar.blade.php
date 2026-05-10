{{-- Citiescapes warm brown sidebar with amber accents --}}
<div class="flex flex-col h-full">
    <div class="p-5 border-b border-amber-500/40">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white shadow-warm bg-gradient-to-tr from-amber-400 to-amber-700">
                <i class="fas {{ auth()->user()->isGm() ? 'fa-user-shield' : 'fa-user' }} text-lg"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-amber-300 truncate">{{ auth()->user()->full_name }}</p>
                <p class="text-xs text-amber-100/70 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
        @auth
            @if(auth()->user()->isGm())
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">Rooms</p>
                <a href="{{ route('admin.rooms.index') }}" class="sidebar-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-door-open"></i> Rooms Management
                </a>
                <a href="{{ route('admin.inquiries.index') }}" class="sidebar-link {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-inbox"></i> Inquiries
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">Tenants</p>
                <a href="{{ route('admin.tenants.index') }}" class="sidebar-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-users"></i> Tenants Records
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">Billing</p>
                <a href="{{ route('admin.billing.index') }}" class="sidebar-link {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-file-invoice-dollar"></i> Billing
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">Contracts</p>
                <a href="{{ route('admin.contracts.index') }}" class="sidebar-link {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-file-contract"></i> Contracts
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">Reports</p>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-clipboard-list"></i> Reports & Archives
                </a>

                <p class="px-3 pt-4 pb-1.5 text-xs font-bold text-amber-300/60 uppercase tracking-wider">System</p>
                <a href="{{ route('admin.audit-log') }}" class="sidebar-link {{ request()->routeIs('admin.audit-log') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-shield-alt"></i> Audit Log
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-cog"></i> Settings
                </a>
            @else
                <a href="{{ route('tenant.dashboard') }}" class="sidebar-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="{{ route('tenant.profile') }}" class="sidebar-link {{ request()->routeIs('tenant.profile') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
                <a href="{{ route('tenant.contract') }}" class="sidebar-link {{ request()->routeIs('tenant.contract') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-file-contract"></i> My Contract
                </a>
                <a href="{{ route('tenant.billing') }}" class="sidebar-link {{ request()->routeIs('tenant.billing') ? 'active' : '' }}" wire:navigate>
                    <i class="fas fa-receipt"></i> My Bills
                </a>
            @endif
        @endauth
    </nav>

    {{-- Brand footer (matching landing logo style) --}}
    <div class="p-4 border-t border-amber-500/40 text-center">
        <p class="brand-text text-lg">CITIESCAPES</p>
        <p class="text-xs text-amber-100/50 mt-1">Apartment Rental</p>
    </div>
</div>
