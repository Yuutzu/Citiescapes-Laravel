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
}
