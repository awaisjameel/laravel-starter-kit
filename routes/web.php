<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

$moduleRouteFiles = [
    base_path('app/Modules/Marketing/Routes/web.php'),
    base_path('app/Modules/Auth/Routes/web.php'),
    base_path('app/Modules/Dashboard/Routes/web.php'),
    base_path('app/Modules/Settings/Routes/web.php'),
    base_path('app/Modules/Users/Routes/web.php'),
];

foreach ($moduleRouteFiles as $moduleRouteFile) {
    if (File::exists($moduleRouteFile)) {
        require $moduleRouteFile;
    }
}
