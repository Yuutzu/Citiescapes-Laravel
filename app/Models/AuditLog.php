<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_type', 'action', 'subsystem',
        'details', 'ip_address',
    ];

    public function user() { return $this->belongsTo(User::class); }

    /**
     * Convenience factory: creates an audit entry.
     */
    public static function record(
        string $action,
        ?int $userId = null,
        ?string $userType = null,
        ?string $subsystem = null,
        ?string $details = null,
        ?string $ip = null,
    ): static {
        return static::create([
            'user_id'    => $userId,
            'user_type'  => $userType,
            'action'     => $action,
            'subsystem'  => $subsystem,
            'details'    => $details,
            'ip_address' => $ip ?? request()?->ip(),
        ]);
    }
}
