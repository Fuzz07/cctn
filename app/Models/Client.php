<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    private static ?bool $archivingSupported = null;
    private static ?bool $subscriptionStatusSupported = null;
    private static ?bool $disconnectionRequestsSupported = null;

    protected $fillable = [
        'account_number', 'firstname', 'middlename', 'lastname', 'birthdate', 'age',
        'place_of_birth', 'gender', 'civil_status', 'address_barangay',
        'address_municipality', 'address_province', 'contact_no', 'email',
        'username', 'password', 'profile_photo', 'proof_of_billing', 'email_verified_at',
        'verification_token', 'reset_token', 'reset_expires_at', 'archived_at',
        'account_status', 'subscription_status', 'current_service_id',
        'current_appointment_id', 'subscription_started_at', 'subscription_ends_at',
        'subscription_cancelled_at', 'disconnection_request_status',
        'disconnection_requested_at', 'disconnection_reviewed_at',
        'disconnection_review_note',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_token', 'reset_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'reset_expires_at'  => 'datetime',
        'archived_at'       => 'datetime',
        'subscription_started_at'   => 'datetime',
        'subscription_ends_at'      => 'datetime',
        'subscription_cancelled_at' => 'datetime',
        'disconnection_requested_at' => 'datetime',
        'disconnection_reviewed_at'  => 'datetime',
        'birthdate'         => 'date',
    ];

    public function scopeActive($query)
    {
        if (static::supportsSubscriptionStatus()) {
            return $query->where('account_status', 'Active');
        }

        return static::supportsArchiving() ? $query->whereNull('archived_at') : $query;
    }

    public function scopeInactive($query)
    {
        if (static::supportsSubscriptionStatus()) {
            return $query->where('account_status', 'Inactive');
        }

        return static::supportsArchiving()
            ? $query->whereNotNull('archived_at')
            : $query->whereRaw('1 = 0');
    }

    public function scopeArchived($query)
    {
        return static::supportsArchiving()
            ? $query->whereNotNull('archived_at')
            : $query->whereRaw('1 = 0');
    }

    /**
     * Archiving was introduced after the first production schema. Keeping this
     * check here lets the admin panel remain available while a deployment is
     * waiting for its database migration to be run.
     */
    public static function supportsArchiving(): bool
    {
        return static::$archivingSupported ??= Schema::hasColumn(
            (new static)->getTable(),
            'archived_at',
        );
    }

    public static function supportsSubscriptionStatus(): bool
    {
        return static::$subscriptionStatusSupported ??= Schema::hasColumn(
            (new static)->getTable(),
            'account_status',
        );
    }

    public static function supportsDisconnectionRequests(): bool
    {
        return static::$disconnectionRequestsSupported ??= Schema::hasColumn(
            (new static)->getTable(),
            'disconnection_request_status',
        );
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isAccountActive(): bool
    {
        if (! static::supportsSubscriptionStatus()) {
            return ! $this->isArchived();
        }

        return $this->account_status === 'Active';
    }

    public function activateSubscription(
        ?Service $service = null,
        ?Appointment $appointment = null,
        $endsAt = null
    ): void {
        if (! static::supportsSubscriptionStatus()) {
            if (static::supportsArchiving()) {
                $this->update(['archived_at' => null]);
            }
            return;
        }

        $attributes = [
            'account_status'            => 'Active',
            'subscription_status'       => 'active',
            'current_service_id'        => $service?->id ?? $this->current_service_id,
            'current_appointment_id'    => $appointment?->id ?? $this->current_appointment_id,
            'subscription_started_at'   => now(),
            'subscription_ends_at'      => $endsAt,
            'subscription_cancelled_at' => null,
            'archived_at'               => null,
        ];

        if (static::supportsDisconnectionRequests()) {
            $attributes += [
                'disconnection_request_status' => null,
                'disconnection_requested_at' => null,
                'disconnection_reviewed_at' => null,
                'disconnection_review_note' => null,
            ];
        }

        $this->update($attributes);
    }

    public function hasPendingDisconnectionRequest(): bool
    {
        return $this->disconnection_request_status === 'pending';
    }

    public function requestDisconnection(): bool
    {
        if (! static::supportsDisconnectionRequests()
            || ! $this->isAccountActive()
            || $this->hasPendingDisconnectionRequest()) {
            return false;
        }

        $updated = static::query()
            ->whereKey($this->getKey())
            ->where('account_status', 'Active')
            ->where(function ($query) {
                $query->whereNull('disconnection_request_status')
                    ->orWhere('disconnection_request_status', '!=', 'pending');
            })
            ->update([
                'disconnection_request_status' => 'pending',
                'disconnection_requested_at' => now(),
                'disconnection_reviewed_at' => null,
                'disconnection_review_note' => null,
                'updated_at' => now(),
            ]);

        $this->refresh();

        return $updated === 1;
    }

    public function approveDisconnection(?string $note = null): bool
    {
        if (! $this->hasPendingDisconnectionRequest()) {
            return false;
        }

        $this->update([
            'disconnection_request_status' => 'approved',
            'disconnection_reviewed_at' => now(),
            'disconnection_review_note' => $note,
        ]);
        $this->deactivateSubscription('cancelled');

        return true;
    }

    public function rejectDisconnection(?string $note = null): bool
    {
        if (! $this->hasPendingDisconnectionRequest()) {
            return false;
        }

        $this->update([
            'disconnection_request_status' => 'rejected',
            'disconnection_reviewed_at' => now(),
            'disconnection_review_note' => $note,
        ]);

        return true;
    }

    public function deactivateSubscription(string $reason = 'cancelled'): void
    {
        $reason = $reason === 'expired' ? 'expired' : 'cancelled';

        if (! static::supportsSubscriptionStatus()) {
            if (static::supportsArchiving()) {
                $this->update(['archived_at' => now()]);
            }
            return;
        }

        $attributes = [
            'account_status'            => 'Inactive',
            'subscription_status'       => $reason,
            'subscription_cancelled_at' => $reason === 'cancelled' ? now() : null,
            'archived_at'               => now(),
        ];

        if (static::supportsDisconnectionRequests() && $this->hasPendingDisconnectionRequest()) {
            $attributes += [
                'disconnection_request_status' => $reason === 'expired' ? 'expired' : 'approved',
                'disconnection_reviewed_at' => now(),
            ];
        }

        $this->update($attributes);

        $this->tokens()->delete();
    }

    public function syncSubscriptionStatus(): bool
    {
        if (! static::supportsSubscriptionStatus()
            || $this->account_status !== 'Active'
            || $this->subscription_status !== 'active'
            || ! $this->subscription_ends_at
            || $this->subscription_ends_at->isFuture()) {
            return false;
        }

        $this->deactivateSubscription('expired');
        return true;
    }

    public static function expireSubscriptions(): int
    {
        if (! static::supportsSubscriptionStatus()) {
            return 0;
        }

        $expired = static::query()
            ->where('account_status', 'Active')
            ->where('subscription_status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<=', now())
            ->get();

        $expired->each->deactivateSubscription('expired');

        return $expired->count();
    }

    public function getSubscriptionStatusLabelAttribute(): string
    {
        return match ($this->subscription_status) {
            'active' => 'Active subscription',
            'expired' => 'Subscription expired',
            'cancelled' => 'Subscription cancelled',
            default => 'No active subscription',
        };
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function currentService()
    {
        return $this->belongsTo(Service::class, 'current_service_id');
    }

    public function currentAppointment()
    {
        return $this->belongsTo(Appointment::class, 'current_appointment_id');
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
