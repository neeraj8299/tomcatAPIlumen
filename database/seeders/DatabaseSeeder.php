<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'participants',
            'moderator'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
            ]);
        }

        $ageGroups = [
            [
                0, 15
            ],
            [
                16, 30
            ]
        ];

        foreach ($ageGroups as $ageGroup) {
            if (count($ageGroup) == 2) {
                AgeGroup::firstOrCreate([
                    'min_age' => $ageGroup[0],
                    'max_age' => $ageGroup[1]
                ]);
            }
        }
    }
}
