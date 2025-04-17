<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $configurator) {
    $configurator->import(
        dirname(__DIR__) . '/src/Modules/Visits/Infrastructure/Controllers',
        'attribute'
    );
};