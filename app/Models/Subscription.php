<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $table = 'subscriptions'; 
    protected $primaryKey = 'subscription_id'; 
    public $incrementing = false; 
    protected $keyType = 'integer';

    protected $fillable = [
        'customer_id',
        'product_id',
        'product_name',
        'frequency',
        'quantity',
        'price_per_unit',
        'start_date',
        'end_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
