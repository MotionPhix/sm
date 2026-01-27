<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'country',
        'region',
        'district',
    ];

    public static function countries(): array
    {
        return self::distinct()
            ->pluck('country')
            ->sort()
            ->values()
            ->map(fn ($country) => [
                'value' => $country,
                'label' => $country,
            ])
            ->toArray();
    }

    public static function regions(string $country): array
    {
        return self::where('country', $country)
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values()
            ->map(fn ($region) => [
                'value' => $region,
                'label' => $region,
            ])
            ->toArray();
    }

    public static function districts(string $country, string $region): array
    {
        return self::where('country', $country)
            ->where('region', $region)
            ->distinct()
            ->pluck('district')
            ->sort()
            ->values()
            ->map(fn ($district) => [
                'value' => $district,
                'label' => $district,
            ])
            ->toArray();
    }
}
