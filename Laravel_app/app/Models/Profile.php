<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'type_identity',
        'identity_number',
        'company_name',
        'CUIT',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'birthdate',
        'bio',
        'website',
        'instagram',
        'twitter',
        'linkedin',
        'github',
        'facebook',
    ];

    /**
     * Get the customer that owns the profile.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
