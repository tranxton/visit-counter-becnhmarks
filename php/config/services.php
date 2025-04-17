<?php

declare(strict_types=1);

use Predis\Client as PredisClient;
use Predis\ClientInterface as PredisClientInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Metrics\MetricsFactory;
use Spiral\RoadRunner\Metrics\MetricsInterface;
use Spiral\RoadRunner\Metrics\MetricsOptions;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $configurator->parameters()->set('.container.dumper.inline_factories', true);

    $services
        ->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->public();

    $services->set(MetricsInterface::class)
        ->factory([inline_service(MetricsFactory::class), 'create'])
        ->arg('$options', inline_service(MetricsOptions::class))
        ->arg(
            '$rpc',
            inline_service(RPC::class)
                ->factory([RPC::class, 'create'])
                ->arg('$connection', '%env(RR_METRIC_EXPORTER_ENDPOINT)%')
        );

    $services->set(PredisClientInterface::class, PredisClient::class)
        ->args([
            [
                'scheme' => 'tcp',
                'host' => '%env(REDIS_HOST)%',
                'port' => '%env(int:REDIS_PORT)%',
                'persistent' => true
            ]
        ]);

    $services->load('App\\', dirname(__DIR__).'/src/')
        ->exclude([
            dirname(__DIR__).'/src/DependencyInjection/',
            dirname(__DIR__).'/src/Entity/',
            dirname(__DIR__).'/src/Kernel.php',
        ]);
};