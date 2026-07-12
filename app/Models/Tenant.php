<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'plan',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function subdomainUrl(): string
    {
        $base   = parse_url(config('app.url'));
        $scheme = $base['scheme'] ?? 'http';
        $host   = $base['host'] ?? 'localhost';
        $port   = isset($base['port']) ? ':' . $base['port'] : '';

        return "{$scheme}://{$this->slug}.{$host}{$port}";
    }

    public static function current(): ?self
    {
        return app()->has('tenant') ? app('tenant') : null;
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'], true);
    }
}
