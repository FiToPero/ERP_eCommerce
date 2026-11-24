<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'type_identity',
        'identity_number',
        'CUIL',
        'phone',
        'position',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'birthdate',
        'marital_status',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
