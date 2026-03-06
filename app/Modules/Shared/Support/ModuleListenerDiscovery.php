<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleListenerDiscovery
{
    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    public static function discoverDirectories(string $basePath, array $priorityModules = []): array
    {
        $normalizedBasePath = mb_rtrim($basePath, '\\/');
        $modulesRoot = $normalizedBasePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';

        if (! is_dir($modulesRoot)) {
            return [];
        }

        $discoveredDirectories = self::discoverListenerDirectories($modulesRoot);
        sort($discoveredDirectories, SORT_STRING);

        $priorityDirectories = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $listenerDirectory = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.'Listeners';

            if (is_dir($listenerDirectory)) {
                $priorityDirectories[] = realpath($listenerDirectory) ?: $listenerDirectory;
            }
        }

        $priorityDirectories = array_values(array_unique($priorityDirectories));
        $priorityLookup = array_fill_keys($priorityDirectories, true);

        $remainingDirectories = array_values(array_filter(
            $discoveredDirectories,
            static fn (string $directory): bool => ! isset($priorityLookup[$directory]),
        ));

        return [...$priorityDirectories, ...$remainingDirectories];
    }

    /**
     * @return list<string>
     */
    private static function discoverListenerDirectories(string $modulesRoot): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $directories = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isDir()) {
                continue;
            }

            if ($file->getFilename() !== 'Listeners') {
                continue;
            }

            $directories[] = $file->getRealPath() ?: $file->getPathname();
        }

        return array_values(array_unique($directories));
    }
}
