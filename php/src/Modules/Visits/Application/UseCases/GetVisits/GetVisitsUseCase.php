<?php

declare(strict_types=1);

namespace App\Modules\Visits\Application\UseCases\GetVisits;

use App\Modules\Visits\Application\Adapters\VisitAdapter;
use App\Modules\Visits\Application\UseCases\GetVisits\Exceptions\GetVisitsException;

final readonly class GetVisitsUseCase
{
    public function __construct(
        private VisitAdapter $visitAdapter
    ) {
    }

    /**
     * @return array<string, string>
     *
     * @throws GetVisitsException
     */
    public function run(): array
    {
        return $this->visitAdapter->get();
    }
}