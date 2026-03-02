<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

$moduleRouteFiles = [
    base_path('app/Modules/Api/V1/Routes/api.php'),
];

foreach ($moduleRouteFiles as $moduleRouteFile) {
    if (File::exists($moduleRouteFile)) {
        require $moduleRouteFile;
    }
}
