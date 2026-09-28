<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['fullname', 'username', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function isSuper(): bool
    {
        return $this->role === 'super_admin';
    }
}
