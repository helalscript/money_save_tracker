<?php

namespace Database\Seeders;

use App\Models\TimePackage;
use Illuminate\Database\Seeder;

class TimePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timePackages = [
            [
                'name' => 'Demo',
                'description' => 'Get 2 days',
                'day' => '2',
                'is_unlimited' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Vacation',
                'description' => 'Get 10 days',
                'day' => '10',
                'is_unlimited' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Annual',
                'description' => 'Get 365 days',
                'day' => '365',
                'is_unlimited' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Permanent',
                'description' => 'For forever days',
                'day' => null,
                'is_unlimited' => true,
                'status' => 'active',
            ],
        ];

        foreach ($timePackages as $package) {
            TimePackage::create($package);
        }
    }
}
