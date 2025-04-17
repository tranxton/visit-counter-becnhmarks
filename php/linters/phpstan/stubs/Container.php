<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection;

class Container
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $id
     *
     * @return T
     */
    public function get(string $id, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {

    }
}