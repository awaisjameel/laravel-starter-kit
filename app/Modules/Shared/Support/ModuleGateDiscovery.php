<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleGateDiscovery
{
    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    public static function discover(string $basePath, array $priorityModules = []): array
    {
        $normalizedBasePath = mb_rtrim($basePath, '\\/');
        $modulesRoot = $normalizedBasePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesRoot)) {
            return [];
        }

        $discoveredGateFiles = self::discoverGateFiles($modulesRoot);
        sort($discoveredGateFiles, SORT_STRING);

        $priorityGateFiles = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $gateFilePath = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'gates.php';

            if (is_file($gateFilePath)) {
                $priorityGateFiles[] = realpath($gateFilePath) ?: $gateFilePath;
            }
        }

        $priorityGateFiles = array_values(array_unique($priorityGateFiles));
        $priorityLookup = array_fill_keys($priorityGateFiles, true);

        $remainingGateFiles = array_values(array_filter(
            $discoveredGateFiles,
            static fn (string $gateFile): bool => ! isset($priorityLookup[$gateFile]),
        ));

        return [...$priorityGateFiles, ...$remainingGateFiles];
    }

    /**
     * @return list<string>
     */
    private static function discoverGateFiles(string $modulesRoot): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $discoveredGateFiles = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getFilename() !== 'gates.php') {
                continue;
            }

            $routesDirectory = $file->getPathInfo();

            if ($routesDirectory->getFilename() !== 'Routes') {
                continue;
            }

            $discoveredGateFiles[] = $file->getRealPath() ?: $file->getPathname();
        }

        return array_values(array_unique($discoveredGateFiles));
    }
}
