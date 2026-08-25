<?php

namespace App\Interface\Service;

interface ShiftServiceInterface
{
    public function findShifts(object $payload);

    public function findShift(string $uuid);

    public function createShift(object $payload);

    public function updateShift(object $payload, string $uuid);

    public function deleteShift(string $uuid);
}
