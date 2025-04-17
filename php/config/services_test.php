<?php

declare(strict_types=1);

use App\Modules\Visits\Infrastructure\Repositories\VisitRepository;
use Predis\ClientInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tests\Stubs\PredisStub;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    // Default settings
    $services
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->public();

    $services->set(VisitRepository::class)
        ->arg('$prefix', 'test:visits:');

    $services->set(ClientInterface::class)
        ->class(PredisStub::class)
        ->arg('$parameters', [
            'scheme' => 'tcp',
            'host' => '%env(REDIS_HOST)%',
            'port' => '%env(int:REDIS_PORT)%'
        ]);
};