<?php

namespace Database\Seeders;

use App\Enums\TabletUnlockCondition;
use App\Models\TabletOfWisdom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TabletsOfWisdomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TabletOfWisdom::create([
            'title' => 'Start thy purse to fattening',
            'body' => '...',
            'sort_order' => 1,
            'unlock_condition' => null,            // open from the start
        ]);

        TabletOfWisdom::create([
            'title' => 'Know where thy gold goes',
            'body' => '...',
            'sort_order' => 2,
            'unlock_condition' => TabletUnlockCondition::FirstImport,
        ]);

        TabletOfWisdom::create([
            'title' => 'Pay thyself first',
            'body' => '...',
            'sort_order' => 3,
            'unlock_condition' => TabletUnlockCondition::PaidYourselfFirst,
            'unlock_threshold' => 10,              // at least 10 percent of income saved
        ]);
    }
}
