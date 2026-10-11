<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'discount_percent',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today());
    }
}
