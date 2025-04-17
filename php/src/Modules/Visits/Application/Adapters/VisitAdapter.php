<?php

declare(strict_types=1);

namespace App\Modules\Visits\Application\Adapters;

use App\Modules\Visits\Application\UseCases\GetVisits\Exceptions\GetVisitsException;
use App\Modules\Visits\Application\UseCases\RegisterVisit\Exceptions\RegisterVisitException;
use App\Modules\Visits\Infrastructure\Repositories\VisitRepository;
use Predis\PredisException;

final readonly class VisitAdapter
{
    public function __construct(
        private VisitRepository $visitRepository
    ) {
    }

    /**
     * @throws RegisterVisitException
     */
    public function add(string $country): int
    {
        try {
            return $this->visitRepository->add($country);
        } catch (PredisException $e) {
            throw RegisterVisitException::fromPredisException(exception: $e);
        }
    }

    /**
     * @return array<string, string>
     *
     * @throws GetVisitsException
     */
    public function get(): array
    {
        try {
            return $this->visitRepository->get();
        } catch (PredisException $e) {
            throw GetVisitsException::fromPredisException(exception: $e);
        }
    }
}