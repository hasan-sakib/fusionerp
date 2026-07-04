<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Confirmed  => 'Confirmed',
            self::Processing => 'Processing',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending    => 'badge-yellow',
            self::Confirmed  => 'badge-blue',
            self::Processing => 'badge-purple',
            self::Completed  => 'badge-green',
            self::Cancelled  => 'badge-red',
        };
    }

    /** @return array<string> */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Pending    => ['confirmed', 'cancelled'],
            self::Confirmed  => ['processing', 'cancelled'],
            self::Processing => ['completed', 'cancelled'],
            self::Completed,
            self::Cancelled  => [],
        };
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    public function isEditable(): bool
    {
        return $this === self::Pending;
    }

    public function isCancellable(): bool
    {
        return !in_array($this, [self::Completed, self::Cancelled], true);
    }
}
