<?php

namespace App\Console\Commands;

use App\Interface\Repository\DailyInventoryRepositoryInterface;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyInventories extends Command
{
    protected $signature = 'inventories:generate {--date= : Business day (Y-m-d); defaults to today in the café timezone}';

    protected $description = "Create each active branch's inventory sheet for the day";

    public function handle(DailyInventoryRepositoryInterface $dailyInventoryRepository): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->format('Y-m-d')
            : now(config('app.business_timezone'))->format('Y-m-d');

        $branches = Branch::where('is_active', true)->get();

        foreach ($branches as $branch) {
            $dailyInventoryRepository->generate($branch->id, $date);
        }

        $this->info("Generated inventory sheets for {$branches->count()} branch(es) on {$date}.");

        return self::SUCCESS;
    }
}
