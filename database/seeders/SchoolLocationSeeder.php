<?php

namespace Database\Seeders;

use App\Models\SchoolLocation;
use Illuminate\Database\Seeder;

class SchoolLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Northern Region
            ['country' => 'Malawi', 'region' => 'Northern', 'district' => 'Chitipa'],
            ['country' => 'Malawi', 'region' => 'Northern', 'district' => 'Karonga'],
            ['country' => 'Malawi', 'region' => 'Northern', 'district' => 'Mzimba'],
            ['country' => 'Malawi', 'region' => 'Northern', 'district' => 'Nkhata Bay'],
            ['country' => 'Malawi', 'region' => 'Northern', 'district' => 'Rumphi'],

            // Central Region
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Dedza'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Dowa'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Kasungu'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Lilongwe'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Mchinji'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Ntcheu'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Ntchisi'],
            ['country' => 'Malawi', 'region' => 'Central', 'district' => 'Salima'],

            // Southern Region
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Balaka'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Blantyre'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Chikwawa'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Chiradzulu'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Machinga'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Mangochi'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Mulanje'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Mwanza'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Nsanje'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Phalombe'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Thyolo'],
            ['country' => 'Malawi', 'region' => 'Southern', 'district' => 'Zomba'],
        ];

        SchoolLocation::insert($locations);
    }
}
