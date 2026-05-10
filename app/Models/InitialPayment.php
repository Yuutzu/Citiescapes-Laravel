<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $tenant_id
 * @property int    $contract_id
 * @property string $deposit_amount
 * @property string $first_month_rent
 * @property string $room_key_fee
 * @property array|null $amenities
 * @property string $amenities_total
 * @property string $total_collected
 * @property \Illuminate\Support\Carbon $date_received
 * @property string $payment_method
 * @property string|null $reference_number
 * @property int    $recorded_by
 */
class InitialPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'contract_id',
        'deposit_amount',
        'first_month_rent',
        'room_key_fee',
        'amenities',
        'amenities_total',
        'total_collected',
        'date_received',
        'payment_method',
        'reference_number',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date_received'    => 'date',
            'deposit_amount'   => 'decimal:2',
            'first_month_rent' => 'decimal:2',
            'room_key_fee'     => 'decimal:2',
            'amenities'        => 'array',
            'amenities_total'  => 'decimal:2',
            'total_collected'  => 'decimal:2',
        ];
    }

    public function tenant()    { return $this->belongsTo(User::class, 'tenant_id'); }
    public function contract()  { return $this->belongsTo(Contract::class); }
    public function recordedBy(){ return $this->belongsTo(User::class, 'recorded_by'); }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'e_wallet'      => 'E-Wallet',
            default         => $this->payment_method,
        };
    }
}
