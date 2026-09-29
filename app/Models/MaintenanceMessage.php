<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'sender_type',
        'sender_id',
        'message',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
