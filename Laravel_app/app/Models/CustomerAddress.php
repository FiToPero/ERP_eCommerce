<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerAddressFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'type',
        'recipient_name',
        'recipient_phone',
        'country',
        'state',
        'city',
        'postal_code',
        'street',
        'street_number',
        'floor',
        'apartment',
        'additional_information',
        'latitude',
        'longitude',
        'is_default_shipping',
        'is_default_billing',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
