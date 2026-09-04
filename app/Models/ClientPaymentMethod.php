<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'payment_type',
        'provider_name',
        'account_name',
        'account_number',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getFormattedTypeAttribute(): string
    {
        return match (strtolower($this->payment_type)) {
            'gcash'         => 'GCash',
            'maya'          => 'Maya',
            'bank_transfer' => 'Bank Transfer',
            'credit_card'   => 'Credit Card',
            'debit_card'    => 'Debit Card',
            default         => ucfirst(str_replace('_', ' ', $this->payment_type)),
        };
    }

    public function getMaskedAccountNumberAttribute(): string
    {
        $num = trim($this->account_number ?? '');
        $len = strlen($num);
        if ($len <= 4) {
            return $num;
        }

        // For mobile numbers e.g. 09171234567 -> 0917 •••• 567
        if ($len === 11 && str_starts_with($num, '09')) {
            return substr($num, 0, 4) . ' •••• ' . substr($num, -3);
        }

        // For card or bank accounts -> •••• 1234
        return '•••• ' . substr($num, -4);
    }

    public function getIconAttribute(): string
    {
        return match (strtolower($this->payment_type)) {
            'gcash', 'maya' => 'bi-phone-fill',
            'bank_transfer' => 'bi-bank',
            'credit_card', 'debit_card' => 'bi-credit-card-2-front-fill',
            default => 'bi-wallet2',
        };
    }

    public function getThemeColorAttribute(): string
    {
        $provider = strtolower($this->provider_name ?? '');
        $type = strtolower($this->payment_type ?? '');

        if (str_contains($provider, 'gcash') || $type === 'gcash') {
            return '#007DFE'; // GCash Blue
        }
        if (str_contains($provider, 'maya') || $type === 'maya') {
            return '#00D064'; // Maya Green
        }
        if (str_contains($provider, 'bdo')) {
            return '#002B66'; // BDO Blue
        }
        if (str_contains($provider, 'bpi')) {
            return '#B3081F'; // BPI Red
        }
        if (str_contains($provider, 'metrobank')) {
            return '#0A2540'; // Metrobank
        }
        if (str_contains($provider, 'unionbank')) {
            return '#E05A10'; // UnionBank Orange
        }

        return '#0f172a';
    }
}
