<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id', 'appointment_id', 'client_id', 'account_number', 'amount_paid',
        'payment_method', 'reference_number', 'received_by', 'notes', 'payment_date', 'receipt_no',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function billing()
    {
        return $this->belongsTo(BillingAccount::class, 'billing_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
