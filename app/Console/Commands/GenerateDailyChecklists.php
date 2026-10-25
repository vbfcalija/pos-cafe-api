<?php

namespace App\Console\Commands;

use App\Interface\Repository\ChecklistRepositoryInterface;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyChecklists extends Command
{
    protected $signature = 'checklists:generate {--date= : Business day (Y-m-d); defaults to today in the café timezone}';

    protected $description = "Create each active branch's opening and closing checklist for the day";

    public function handle(ChecklistRepositoryInterface $checklistRepository): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->format('Y-m-d')
            : now(config('app.business_timezone'))->format('Y-m-d');

        $branches = Branch::where('is_active', true)->get();

        foreach ($branches as $branch) {
            $checklistRepository->generate($branch->id, $date);
        }

        $this->info("Generated checklists for {$branches->count()} branch(es) on {$date}.");

        return self::SUCCESS;
    }
}
