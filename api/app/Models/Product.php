<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'shopify_id',
        'title',
        'description',
        'price',
        'variant_id',
        'inventory_item_id'
    ];
}
