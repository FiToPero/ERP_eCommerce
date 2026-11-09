<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'company_name',
        'cuit',
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
