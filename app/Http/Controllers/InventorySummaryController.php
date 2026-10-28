<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventorySummary\InventorySummaryRequest;
use App\Interface\Service\InventorySummaryServiceInterface;

class InventorySummaryController extends Controller
{
    private $inventorySummaryService;

    public function __construct(InventorySummaryServiceInterface $inventorySummaryService)
    {
        $this->inventorySummaryService = $inventorySummaryService;
    }

    public function index(InventorySummaryRequest $request)
    {
        return $this->inventorySummaryService->summarize($request);
    }
}
