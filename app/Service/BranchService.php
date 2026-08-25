<?php

namespace App\Service;

use App\Http\Resources\BranchResource;
use App\Interface\Repository\BranchRepositoryInterface;
use App\Interface\Service\BranchServiceInterface;
use App\Traits\SortingTraits;

class BranchService implements BranchServiceInterface
{
    use SortingTraits;

    private $branchRepository;

    public function __construct(BranchRepositoryInterface $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    public function findBranches(object $payload)
    {
        $sortField = $this->sortField($payload, 'name');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $branches = $this->branchRepository->findMany($payload, $sortField, $sortOrder);

        return BranchResource::collection($branches);
    }

    public function findBranch(string $uuid)
    {
        $branch = $this->branchRepository->findByUuid($uuid);

        return new BranchResource($branch);
    }

    public function createBranch(object $payload)
    {
        $branch = $this->branchRepository->create($payload);

        return new BranchResource($branch);
    }

    public function updateBranch(object $payload, string $uuid)
    {
        $branch = $this->branchRepository->update($payload, $uuid);

        return new BranchResource($branch);
    }

    public function deleteBranch(string $uuid)
    {
        $this->branchRepository->delete($uuid);

        return response()->json([
            'message' => 'Branch deleted successfully',
        ], 200);
    }
}
