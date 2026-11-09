<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'currency',
        'expires_at',
        'converted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
