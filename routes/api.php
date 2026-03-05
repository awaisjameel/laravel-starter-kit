<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleRouteDiscovery;

$moduleRouteFiles = ModuleRouteDiscovery::discover(
    basePath: base_path(),
    routeType: 'api',
    priorityModules: [
        'Api/V1',
    ],
);

foreach ($moduleRouteFiles as $moduleRouteFile) {
    require $moduleRouteFile;
}
