<?php

namespace App\Interface\Service;

interface ActivityLogServiceInterface
{
    public function findActivityLogs(object $payload);

    public function findActivityLog(string $uuid);
}
