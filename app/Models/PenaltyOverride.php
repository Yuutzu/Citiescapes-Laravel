<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyOverride extends Model
{
    protected $fillable = [
        'bill_id', 'overridden_by', 'original_penalty',
        'adjusted_penalty', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'original_penalty' => 'decimal:2',
            'adjusted_penalty' => 'decimal:2',
        ];
    }

    public function bill()         { return $this->belongsTo(Bill::class); }
    public function overriddenBy() { return $this->belongsTo(User::class, 'overridden_by'); }
}
