<?php

namespace Database\Seeders;

use App\Models\Institution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            ['ING', 'bank', 'NL'],
            ['Rabobank', 'bank', 'NL'],
            ['ABN AMRO', 'bank', 'NL'],
            ['bunq', 'bank', 'NL'],
            ['DEGIRO', 'broker', 'NL'],
            ['Bitvavo', 'crypto', 'NL'],
        ];

        foreach ($institutions as [$name, $type, $country]) {
            Institution::firstOrCreate(
                ['name' => $name, 'country' => $country],
                ['type' => $type],
            );
        }
    }
}
