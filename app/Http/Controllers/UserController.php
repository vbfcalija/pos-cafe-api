<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Interface\Service\UserServiceInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        return $this->userService->findUsers($request);
    }

    public function store(StoreUserRequest $request)
    {
        return $this->userService->createUser($request);
    }

    public function show(string $uuid)
    {
        return $this->userService->findUser($uuid);
    }

    public function update(UpdateUserRequest $request, string $uuid)
    {
        return $this->userService->updateUser($request, $uuid);
    }

    public function destroy(string $uuid)
    {
        return $this->userService->deleteUser($uuid);
    }
}
