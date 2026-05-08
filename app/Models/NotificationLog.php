<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notifications_log';

    protected $fillable = ['user_id', 'type', 'source', 'message', 'is_read'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function user() { return $this->belongsTo(User::class); }

    public function scopeUnread($q) { return $q->where('is_read', false); }

    public function actionUrl(?string $role = null): ?string
    {
        $role ??= $this->user?->role;

        return match ($this->type) {
            'new_inquiry'                                 => route('admin.inquiries.index'),
            'tenant_request'                              => route('admin.requests.index'),
            'request_response'                            => route('tenant.requests'),
            'account_locked'                              => route('admin.audit-log'),
            'grace_reminder',
            'delinquent_notice',
            'eviction_notice'                             => route('tenant.billing'),
            'announcement'                                => $role === 'gm'
                ? route('admin.announcements.index')
                : route('tenant.dashboard'),
            '30_day_warning', '7_day_warning'             => $role === 'gm'
                ? route('admin.contracts.index')
                : route('tenant.contract'),
            default                                       => null,
        };
    }
}
