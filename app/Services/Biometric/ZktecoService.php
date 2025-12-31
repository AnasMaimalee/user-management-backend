<?php

// app/Services/Biometric/ZktecoService.php

namespace App\Services\Biometric;

use Jmrashed\Zkteco\Lib\ZKTeco;

class ZktecoService
{
    protected ZKTeco $zk;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->zk = new ZKTeco($ip, $port);
    }

    public function connect(): bool
    {
        return $this->zk->connect();
    }

    public function getAttendance(): array
    {
        return $this->zk->getAttendance() ?? [];
    }

    public function disconnect(): void
    {
        $this->zk->disconnect();
    }
}
