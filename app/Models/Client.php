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
            return $query
                ->where('account_status', 'Active')
                ->where('subscription_status', 'active')
                ->whereNull('archived_at');
        }

        return static::supportsArchiving() ? $query->whereNull('archived_at') : $query;
    }

    public function scopeInactive($query)
    {
        if (static::supportsSubscriptionStatus()) {
            return $query->where(function ($statusQuery) {
                $statusQuery->where('account_status', 'Inactive')
                    ->orWhere('subscription_status', '!=', 'active')
                    ->orWhereNull('subscription_status')
                    ->orWhereNotNull('archived_at');
            });
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

        return $this->account_status === 'Active'
            && $this->subscription_status === 'active'
            && ! $this->isArchived();
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

    public function isDisconnected(): bool
    {
        return $this->disconnection_request_status === 'approved';
    }

    /**
     * Disconnection is tracked separately from the subscription: any client
     * with a service on record may ask for it, whether the subscription is
     * Active or Inactive, until it is pending or already carried out.
     */
    public function canRequestDisconnection(): bool
    {
        return static::supportsDisconnectionRequests()
            && $this->current_service_id !== null
            && ! $this->hasPendingDisconnectionRequest()
            && ! $this->isDisconnected();
    }

    /** Cancel the current plan. The account and its history stay, and the client may subscribe again. */
    public function unsubscribe(): bool
    {
        if (! $this->isAccountActive()) {
            return false;
        }

        $this->deactivateSubscription('cancelled');

        return true;
    }

    public function requestDisconnection(): bool
    {
        if (! $this->canRequestDisconnection()) {
            return false;
        }

        $updated = static::query()
            ->whereKey($this->getKey())
            ->whereNotNull('current_service_id')
            ->where(function ($query) {
                $query->whereNull('disconnection_request_status')
                    ->orWhereNotIn('disconnection_request_status', ['pending', 'approved']);
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

        // A disconnected line cannot keep an Active plan, but a subscription
        // that already ended keeps the reason it ended with.
        if ($this->isAccountActive()) {
            $this->deactivateSubscription('cancelled');
        }

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

    /**
     * Mark the subscription Inactive. archived_at records when that happened;
     * it does not lock the client out, so they can still sign in and choose a
     * new plan. Any disconnection request is left exactly as it was.
     */
    public function deactivateSubscription(string $reason = 'cancelled'): void
    {
        $reason = $reason === 'expired' ? 'expired' : 'cancelled';

        if (! static::supportsSubscriptionStatus()) {
            if (static::supportsArchiving()) {
                $this->update(['archived_at' => now()]);
            }
            return;
        }

        $this->update([
            'account_status'            => 'Inactive',
            'subscription_status'       => $reason,
            'subscription_cancelled_at' => $reason === 'cancelled' ? now() : null,
            'archived_at'               => now(),
        ]);
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

    public function getDisconnectionStatusLabelAttribute(): string
    {
        return match ($this->disconnection_request_status) {
            'pending' => 'Disconnection requested',
            'approved' => 'Service disconnected',
            'rejected' => 'Disconnection request declined',
            default => $this->current_service_id ? 'Connected' : 'No connected service',
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
