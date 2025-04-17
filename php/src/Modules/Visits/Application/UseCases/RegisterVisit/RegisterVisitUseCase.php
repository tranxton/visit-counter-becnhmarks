<?php

declare(strict_types=1);

namespace App\Modules\Visits\Application\UseCases\RegisterVisit;

use App\Modules\Visits\Application\Adapters\VisitAdapter;
use App\Modules\Visits\Application\UseCases\RegisterVisit\Exceptions\RegisterVisitException;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RegisterVisitUseCase
{
    public function __construct(
        private ValidatorInterface $validator,
        private VisitAdapter $visitAdapter,
    ) {
    }

    /**
     * @throws RegisterVisitException
     */
    public function run(string $country): int
    {
        $country = $this->validate(country: $country);

        return $this->incr(country: $country);
    }

    /**
     * @return non-empty-string
     *
     * @throws RegisterVisitException
     */
    private function validate(string $country): string
    {
        $violations = $this->validator->validate($country, new Country);
        if ($violations->count() > 0) {
            throw RegisterVisitException::fromViolations(violations: $violations);
        }

        /** @var non-empty-string */
        return $country;
    }

    /**
     * @param  non-empty-string  $country
     *
     * @throws RegisterVisitException
     */
    private function incr(string $country): int
    {
        return $this->visitAdapter->add($country);
    }
}