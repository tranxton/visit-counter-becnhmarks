<?php

declare(strict_types=1);

namespace App\Modules\Visits\Application\UseCases\RegisterVisit\Exceptions;

use Exception;
use Predis\PredisException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RegisterVisitException extends Exception
{
    public static function fromViolations(ConstraintViolationListInterface $violations): self
    {
        return new self(message: (string) $violations);
    }

    public static function fromPredisException(PredisException $exception): self
    {
        return new self(message: 'Something went wrong while registering visits.', previous: $exception);
    }
}