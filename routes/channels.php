<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleChannelDiscovery;

$moduleChannelFiles = ModuleChannelDiscovery::discover(
    basePath: base_path(),
    priorityModules: [
        'Shared',
        'Users',
    ],
);

foreach ($moduleChannelFiles as $moduleChannelFile) {
    require $moduleChannelFile;
}
