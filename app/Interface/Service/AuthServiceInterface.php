<?php

namespace App\Interface\Service;

interface AuthServiceInterface
{
    public function login(object $payload);

    public function register(object $payload);

    public function logout(object $payload);
}
