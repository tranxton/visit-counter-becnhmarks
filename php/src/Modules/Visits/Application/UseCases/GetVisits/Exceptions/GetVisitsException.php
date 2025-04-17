<?php

declare(strict_types=1);

namespace App\Modules\Visits\Application\UseCases\GetVisits\Exceptions;

use Exception;
use Predis\PredisException;

class GetVisitsException extends Exception
{
    public static function fromPredisException(PredisException $exception): self
    {
        return new self(message: 'Something went wrong getting visits statistics.', previous: $exception);
    }
}