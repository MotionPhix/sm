<?php

namespace App\Enums;

enum SchoolType: string
{
    case PublicPrimary = 'public_primary';
    case PrivatePrimary = 'private_primary';
    case PublicSecondary = 'public_secondary';
    case PrivateSecondary = 'private_secondary';
    case CombinedPublic = 'combined_public';
    case CombinedPrivate = 'combined_private';

    public function label(): string
    {
        return match ($this) {
            self::PublicPrimary => 'Public Primary School',
            self::PrivatePrimary => 'Private Primary School',
            self::PublicSecondary => 'Public Secondary School',
            self::PrivateSecondary => 'Private Secondary School',
            self::CombinedPublic => 'Combined Public School',
            self::CombinedPrivate => 'Combined Private School',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->toArray();
    }
}
