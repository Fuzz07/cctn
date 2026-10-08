<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_ref', 'is_walkin', 'client_id', 'service_id', 'installation_type', 'preferred_date', 'preferred_time',
        'installation_address', 'installation_municipality', 'installation_barangay', 'purok_landmark', 'message', 'status', 'installation_status', 'payment_status',
        'payment_method', 'amount_paid', 'amount_due', 'change_amount', 'bank_name', 'reference_number',
        'due_date', 'subscription_ends_at', 'payment_date', 'admin_notes', 'proof_of_address', 'valid_id', 'valid_id_type',
        'valid_id_number', 'payment_proof',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'preferred_time' => 'datetime:H:i:s',
        'due_date'       => 'date',
        'subscription_ends_at' => 'datetime',
        'payment_date'   => 'datetime',
        'is_walkin'      => 'boolean',
        'amount_paid'    => 'decimal:2',
        'amount_due'     => 'decimal:2',
        'change_amount'  => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public static function hasConflict(string $date, string $time, int $excludeId = 0): bool
    {
        $query = self::where('preferred_date', $date)
            ->where('preferred_time', $time)
            ->where('status', '!=', 'cancelled');

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
