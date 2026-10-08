<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    public const INSTALLATION_TYPES = ['residential', 'business'];

    protected $fillable = ['service_name', 'speed', 'description', 'duration_minutes', 'price', 'installation_fee', 'status', 'account_type'];

    protected $casts = [
        'price'            => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /** Plans offered to the given installation type, plus the ones offered to both. */
    public function scopeForAccountType($query, $type)
    {
        if (! in_array($type, self::INSTALLATION_TYPES, true)) {
            return $query;
        }

        return $query->whereIn('account_type', [$type, 'both']);
    }

    /** True when this plan may be booked under the given installation type. */
    public function availableTo($type)
    {
        return $this->account_type === 'both' || $this->account_type === $type;
    }
}
