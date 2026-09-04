<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'account_number', 'firstname', 'middlename', 'lastname', 'birthdate', 'age',
        'place_of_birth', 'gender', 'civil_status', 'address_barangay',
        'address_municipality', 'address_province', 'contact_no', 'email',
        'username', 'password', 'profile_photo', 'proof_of_billing', 'email_verified_at',
        'verification_token', 'reset_token', 'reset_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_token', 'reset_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'reset_expires_at'  => 'datetime',
        'birthdate'         => 'date',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function billingAccounts()
    {
        return $this->hasMany(BillingAccount::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function paymentMethods()
    {
        ClientPaymentMethod::ensureTableExists();
        return $this->hasMany(ClientPaymentMethod::class);
    }

    public function defaultPaymentMethod()
    {
        ClientPaymentMethod::ensureTableExists();
        return $this->hasOne(ClientPaymentMethod::class)->where('is_default', true);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname}");
    }

    public function getCompleteAddressAttribute(): string
    {
        $parts = array_filter([$this->address_barangay, $this->address_municipality, $this->address_province]);
        return !empty($parts) ? implode(', ', $parts) : 'N/A';
    }

    /**
     * Generate the next account number in the YYYY-MM-NN series (e.g. 2026-01-01),
     * where NN is a per-month sequence that continues from the highest issued number.
     */
    public static function nextAccountNumber(): string
    {
        $prefix = now()->format('Y-m') . '-';

        $lastSequence = static::where('account_number', 'like', $prefix . '%')
            ->pluck('account_number')
            ->map(fn ($number) => (int) substr($number, strlen($prefix)))
            ->max() ?? 0;

        return $prefix . str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);
    }
}
