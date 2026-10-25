<?php

namespace Database\Seeders;

use App\Enums\ChecklistType;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * The Brthrs Café barista daily checklist, in the order it's printed.
     * Tasks are listed on a checklist in creation (id) order.
     */
    private const OPENER = [
        'Open café; check lights, aircon & fans',
        'Wash & sanitize hands',
        'Turn on espresso machine & grinder',
        'Check water supply',
        'Clean & sanitize portafilter and group head',
        'Flush group head',
        'Clean & check steam wand',
        'Check grinder settings',
        'Dial-in espresso — dose, yield & extraction time',
        'Prepare ice, milk & other ingredients',
        'Refill coffee beans & matcha',
        'Refill syrups & sauces',
        'Refill cups, lids, straws, napkins & tissue',
        'Check pastries / food display',
        'Check refrigerator / freezer',
        'Clean & sanitize counters',
        'Clean tables & service area',
        'Empty / replace trash if needed',
        'Check POS & receipt paper',
        'Check menu / price signs',
        'Check restroom',
        'Final check: CLEAN • COMPLETE • READY TO SERVE',
    ];

    private const CLOSER = [
        'Close orders according to closing time',
        'Backflush & clean espresso machine',
        'Clean portafilters & baskets',
        'Clean & purge steam wand',
        'Clean grinder & hopper',
        'Empty & clean knock box',
        'Wash & sanitize pitchers, shakers & bar tools',
        'Store coffee beans properly',
        'Store milk & ingredients properly',
        'Check expiration dates / FIFO',
        'Clean & sanitize all counters',
        'Clean sink & bar area',
        'Clean refrigerator handles / exterior',
        'Sweep & mop floor',
        'Empty all trash bins',
        'Restock supplies for next shift',
        'Clean tables & chairs',
        'Clean / check restroom',
        'Count / check cash & POS sales',
        'Turn off unnecessary equipment',
        'Check water / electricity / gas if applicable',
        'Turn off lights, aircon & fans',
        'Lock doors & windows',
        'Final walk-through: CLEAN • SAFE • READY FOR TOMORROW',
    ];

    public function run(): void
    {
        foreach ([ChecklistType::Opener->value => self::OPENER, ChecklistType::Closer->value => self::CLOSER] as $type => $names) {
            foreach ($names as $name) {
                Task::query()->firstOrCreate(['name' => $name, 'type' => $type]);
            }
        }
    }
}
