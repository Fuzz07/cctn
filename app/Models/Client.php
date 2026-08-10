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

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname}");
    }
}
