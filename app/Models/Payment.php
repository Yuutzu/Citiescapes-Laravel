<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'bill_id', 'tenant_id', 'amount', 'payment_method',
        'reference_number', 'notes', 'confirmed_by', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function bill()        { return $this->belongsTo(Bill::class); }
    public function tenant()      { return $this->belongsTo(User::class, 'tenant_id'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
