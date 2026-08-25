<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Interface\Service\BranchServiceInterface;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    private $branchService;

    public function __construct(BranchServiceInterface $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request)
    {
        return $this->branchService->findBranches($request);
    }

    public function store(StoreBranchRequest $request)
    {
        return $this->branchService->createBranch($request);
    }

    public function show(string $uuid)
    {
        return $this->branchService->findBranch($uuid);
    }

    public function update(UpdateBranchRequest $request, string $uuid)
    {
        return $this->branchService->updateBranch($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->branchService->deleteBranch($uuid);
    }
}
