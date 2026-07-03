<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'    => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
