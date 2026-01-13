<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'name',
        'sku',
        'price',
        'quantity',
        'warranty_months',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'int',
        'warranty_months' => 'int',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
