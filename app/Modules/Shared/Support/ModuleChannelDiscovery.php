<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleChannelDiscovery
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

        $discoveredChannelFiles = self::discoverChannelFiles($modulesRoot);
        sort($discoveredChannelFiles, SORT_STRING);

        $priorityChannelFiles = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $channelFilePath = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'channels.php';

            if (is_file($channelFilePath)) {
                $priorityChannelFiles[] = realpath($channelFilePath) ?: $channelFilePath;
            }
        }

        $priorityChannelFiles = array_values(array_unique($priorityChannelFiles));
        $priorityLookup = array_fill_keys($priorityChannelFiles, true);

        $remainingChannelFiles = array_values(array_filter(
            $discoveredChannelFiles,
            static fn (string $channelFile): bool => ! isset($priorityLookup[$channelFile]),
        ));

        return [...$priorityChannelFiles, ...$remainingChannelFiles];
    }

    /**
     * @return list<string>
     */
    private static function discoverChannelFiles(string $modulesRoot): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $discoveredChannelFiles = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getFilename() !== 'channels.php') {
                continue;
            }

            $routesDirectory = $file->getPathInfo();

            if ($routesDirectory->getFilename() !== 'Routes') {
                continue;
            }

            $discoveredChannelFiles[] = $file->getRealPath() ?: $file->getPathname();
        }

        return array_values(array_unique($discoveredChannelFiles));
    }
}
