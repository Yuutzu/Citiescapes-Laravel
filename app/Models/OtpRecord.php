<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRecord extends Model
{
    protected $fillable = ['user_id', 'code', 'expires_at', 'used'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used' => 'boolean'];
    }

    public function user() { return $this->belongsTo(User::class); }

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
