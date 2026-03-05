<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleRouteDiscovery;

$moduleRouteFiles = ModuleRouteDiscovery::discover(
    basePath: base_path(),
    routeType: 'web',
    priorityModules: [
        'Marketing',
        'Auth',
        'Dashboard',
        'Settings',
        'Users',
    ],
);

foreach ($moduleRouteFiles as $moduleRouteFile) {
    require $moduleRouteFile;
}
