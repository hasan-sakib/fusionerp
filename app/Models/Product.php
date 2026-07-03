<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'price',
        'cost',
        'image',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'cost'        => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }
}
