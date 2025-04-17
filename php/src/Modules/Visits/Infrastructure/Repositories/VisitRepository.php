<?php

declare(strict_types=1);

namespace App\Modules\Visits\Infrastructure\Repositories;

use Predis\ClientInterface as RedisClient;

final readonly class VisitRepository
{
    public function __construct(
        private RedisClient $redis,
        private string $prefix = 'visits:',
    ) {
    }

    public function add(string $country): int
    {
        return $this->redis->hincrby(key: $this->prefix, field: $country, increment: 1);
    }

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        /** @var array<string, string> */
        return $this->redis->hgetall($this->prefix);
    }
}