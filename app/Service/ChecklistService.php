<?php

namespace App\Service;

use App\Enums\ChecklistStatus;
use App\Enums\ChecklistType;
use App\Http\Resources\ChecklistResource;
use App\Interface\Repository\ChecklistRepositoryInterface;
use App\Interface\Service\ChecklistServiceInterface;
use App\Models\Branch;
use App\Models\Checklist;
use App\Models\User;
use App\Traits\SortingTraits;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ChecklistService implements ChecklistServiceInterface
{
    use SortingTraits;

    private $checklistRepository;

    public function __construct(ChecklistRepositoryInterface $checklistRepository)
    {
        $this->checklistRepository = $checklistRepository;
    }

    public function findChecklists(object $payload)
    {
        $sortField = $this->sortField($payload, 'date');
        $sortOrder = $this->sortOrder($payload, 'desc');

        $checklists = $this->checklistRepository->findMany($payload, $sortField, $sortOrder);

        return ChecklistResource::collection($checklists);
    }

    public function findChecklist(string $uuid)
    {
        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    /**
     * Opens (creating if needed) a branch's checklist for a day — today in
     * the café's timezone unless a date is given.
     */
    public function generateChecklist(object $payload)
    {
        $branch = Branch::where('uuid', $payload->branch_uuid)->firstOrFail();
        $date = $payload->date
            ? Carbon::parse($payload->date)->format('Y-m-d')
            : now(config('app.business_timezone'))->format('Y-m-d');

        $checklist = $this->checklistRepository->generate($branch->id, $date);

        return new ChecklistResource($this->checklistRepository->findByUuid($checklist->uuid));
    }

    public function updateChecklist(object $payload, string $uuid)
    {
        $checklist = $this->checklistRepository->findByUuid($uuid);
        $verifying = $payload->status === ChecklistStatus::Verified->value && ! $checklist->isVerified();

        // A verified checklist can only be reopened; nothing else on it changes.
        if ($checklist->isVerified() && $payload->status !== ChecklistStatus::Pending->value) {
            return $this->lockedResponse();
        }

        if ($verifying && ($problem = $this->verificationProblem($checklist))) {
            return response()->json(['message' => $problem], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->checklistRepository->update($checklist, $payload);

        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    public function updateTaskCompletion(object $payload, string $uuid, string $taskCompletionUuid)
    {
        $checklist = $this->checklistRepository->findByUuid($uuid);

        if ($checklist->isVerified()) {
            return $this->lockedResponse();
        }

        $this->checklistRepository->updateTaskCompletion($checklist, $taskCompletionUuid, $payload);

        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    public function addUser(object $payload, string $uuid)
    {
        $checklist = $this->checklistRepository->findByUuid($uuid);

        if ($checklist->isVerified()) {
            return $this->lockedResponse();
        }

        $user = User::where('uuid', $payload->user_uuid)->firstOrFail();
        $this->checklistRepository->addUser($checklist, $user->id, $payload->type);

        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    public function removeUser(string $uuid, string $checklistUserUuid)
    {
        $checklist = $this->checklistRepository->findByUuid($uuid);

        if ($checklist->isVerified()) {
            return $this->lockedResponse();
        }

        $this->checklistRepository->removeUser($checklist, $checklistUserUuid);

        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    /**
     * Sets every opener (or closer) in one go, from a multi-select.
     */
    public function syncUsers(object $payload, string $uuid)
    {
        $checklist = $this->checklistRepository->findByUuid($uuid);

        if ($checklist->isVerified()) {
            return $this->lockedResponse();
        }

        $userIds = User::whereIn('uuid', $payload->user_uuids ?? [])->pluck('id')->all();
        $this->checklistRepository->syncUsers($checklist, $payload->type, $userIds);

        return new ChecklistResource($this->checklistRepository->findByUuid($uuid));
    }

    /**
     * A checklist is verified only once every task is ticked and both halves
     * of the day have at least one barista named on them.
     */
    private function verificationProblem(Checklist $checklist): ?string
    {
        $remaining = $checklist->taskCompletions->where('is_done', false)->count();

        if ($remaining > 0) {
            return "{$remaining} task" . ($remaining === 1 ? ' is' : 's are') . ' not done yet. Tick every task (add a remark if one didn\'t apply) before verifying.';
        }

        foreach ([ChecklistType::Opener, ChecklistType::Closer] as $type) {
            if (! $checklist->checklistUsers->contains('type', $type)) {
                return "Add at least one {$type->value} before verifying.";
            }
        }

        return null;
    }

    private function lockedResponse()
    {
        return response()->json([
            'message' => 'This checklist has been verified. Reopen it to make changes.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
