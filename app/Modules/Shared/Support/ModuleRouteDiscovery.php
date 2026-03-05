<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleRouteDiscovery
{
    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    public static function discover(string $basePath, string $routeType, array $priorityModules = []): array
    {
        $normalizedRouteType = mb_strtolower(mb_trim($routeType));

        if (! in_array($normalizedRouteType, ['web', 'api'], true)) {
            throw new InvalidArgumentException('Route type must be either "web" or "api".');
        }

        $normalizedBasePath = mb_rtrim($basePath, '\\/');
        $modulesRoot = $normalizedBasePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesRoot)) {
            return [];
        }

        $discoveredRouteFiles = self::discoverRouteFiles($modulesRoot, $normalizedRouteType);
        sort($discoveredRouteFiles, SORT_STRING);

        $priorityRouteFiles = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $routeFilePath = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.$normalizedRouteType.'.php';

            if (is_file($routeFilePath)) {
                $priorityRouteFiles[] = realpath($routeFilePath) ?: $routeFilePath;
            }
        }

        $priorityRouteFiles = array_values(array_unique($priorityRouteFiles));
        $priorityLookup = array_fill_keys($priorityRouteFiles, true);

        $remainingRoutes = array_values(array_filter(
            $discoveredRouteFiles,
            static fn (string $routeFile): bool => ! isset($priorityLookup[$routeFile]),
        ));

        return [...$priorityRouteFiles, ...$remainingRoutes];
    }

    /**
     * @return list<string>
     */
    private static function discoverRouteFiles(string $modulesRoot, string $routeType): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $discoveredRouteFiles = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getFilename() !== $routeType.'.php') {
                continue;
            }

            $routesDirectory = $file->getPathInfo();

            if ($routesDirectory->getFilename() !== 'Routes') {
                continue;
            }

            $discoveredRouteFiles[] = $file->getRealPath() ?: $file->getPathname();
        }

        return array_values(array_unique($discoveredRouteFiles));
    }
}
