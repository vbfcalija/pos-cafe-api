<?php

namespace App\Service;

use App\Http\Resources\UserResource;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\UserServiceInterface;
use App\Traits\SortingTraits;

class UserService implements UserServiceInterface
{
    use SortingTraits;

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findUsers(object $payload)
    {
        $sortField = $this->sortField($payload, 'firstname');
        $sortOrder = $this->sortOrder($payload, 'asc');

        $users = $this->userRepository->findMany($payload, $sortField, $sortOrder);

        return UserResource::collection($users);
    }

    public function findUser(string $uuid)
    {
        $user = $this->userRepository->findByUuid($uuid);

        return new UserResource($user);
    }

    public function createUser(object $payload)
    {
        $user = $this->userRepository->create($payload);

        return new UserResource($user);
    }

    public function updateUser(object $payload, string $uuid)
    {
        $user = $this->userRepository->update($payload, $uuid);

        return new UserResource($user);
    }

    public function deleteUser(string $uuid)
    {
        $this->userRepository->delete($uuid);

        return response()->json([
            'message' => 'Success.',
        ], 200);
    }
}
