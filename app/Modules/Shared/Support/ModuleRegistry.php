<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleRegistry
{
    private const int CACHE_VERSION = 2;

    private const string CACHE_RELATIVE_PATH = 'bootstrap/cache/modules.php';

    /**
     * @var array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private const array DEFAULT_PRIORITY_MODULES = [
        'routes' => [
            'web' => ['Marketing', 'Auth', 'Dashboard', 'Settings', 'Users'],
            'api' => ['Api/V1'],
        ],
        'gates' => ['Users'],
        'channels' => ['Shared', 'Users'],
        'listeners' => ['Users'],
        'providers' => [],
    ];

    /**
     * @return list<string>
     */
    public static function webRoutes(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['routes']['web'];

        return $entries;
    }

    /**
     * @return list<string>
     */
    public static function apiRoutes(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['routes']['api'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function routeFiles(string $basePath, string $routeType, ?array $priorityModules = null): array
    {
        $normalizedRouteType = self::normalizeRouteType($routeType);

        if ($priorityModules === null) {
            return $normalizedRouteType === 'web'
                ? self::webRoutes($basePath)
                : self::apiRoutes($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                routeType: $normalizedRouteType,
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function gateFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['gates'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function gateFilesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::gateFiles($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                filename: 'gates.php',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function channelFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['channels'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function channelFilesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::channelFiles($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                filename: 'channels.php',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function listenerDirectories(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['listeners'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function listenerDirectoriesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::listenerDirectories($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                directoryName: 'Listeners',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function providerFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['providers'];

        return $entries;
    }

    /**
     * @return list<class-string<ServiceProvider>>
     */
    public static function providerClasses(string $basePath): array
    {
        $classes = array_values(array_filter(
            array_map(
                static fn (string $providerFile): ?string => self::providerClassFromPath($basePath, $providerFile),
                self::providerFiles($basePath),
            ),
            static fn (?string $providerClass): bool => $providerClass !== null,
        ));

        return array_values(array_unique($classes));
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    public static function buildCachePayload(string $basePath): array
    {
        return self::scanPayload(self::normalizeBasePath($basePath));
    }

    public static function cachePath(string $basePath): string
    {
        return self::normalizeBasePath($basePath).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, self::CACHE_RELATIVE_PATH);
    }

    public static function cacheFileContents(string $basePath): string
    {
        return "<?php\n\nreturn ".var_export(self::buildCachePayload($basePath), true).";\n";
    }

    /**
     * @return array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    public static function discover(string $basePath): array
    {
        $normalizedBasePath = self::normalizeBasePath($basePath);
        $payload = self::loadCachedPayload($normalizedBasePath) ?? self::scanPayload($normalizedBasePath);

        return self::resolvePayloadEntries($normalizedBasePath, $payload);
    }

    private static function normalizeRouteType(string $routeType): string
    {
        $normalizedRouteType = mb_strtolower(mb_trim($routeType));

        if (! in_array($normalizedRouteType, ['web', 'api'], true)) {
            throw new InvalidArgumentException('Route type must be either "web" or "api".');
        }

        return $normalizedRouteType;
    }

    private static function normalizeBasePath(string $basePath): string
    {
        return mb_rtrim($basePath, '\\/');
    }

    private static function modulesRoot(string $basePath): string
    {
        return self::normalizeBasePath($basePath).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function scanPayload(string $basePath): array
    {
        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return self::emptyPayload();
        }

        return [
            'version' => self::CACHE_VERSION,
            'routes' => [
                'web' => self::scanRouteEntries(
                    modulesRoot: $modulesRoot,
                    basePath: $basePath,
                    routeType: 'web',
                    priorityModules: self::DEFAULT_PRIORITY_MODULES['routes']['web'],
                ),
                'api' => self::scanRouteEntries(
                    modulesRoot: $modulesRoot,
                    basePath: $basePath,
                    routeType: 'api',
                    priorityModules: self::DEFAULT_PRIORITY_MODULES['routes']['api'],
                ),
            ],
            'gates' => self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                filename: 'gates.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['gates'],
            ),
            'channels' => self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                filename: 'channels.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['channels'],
            ),
            'listeners' => self::scanNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Listeners',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['listeners'],
            ),
            'providers' => self::scanNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Providers',
                filename: 'ModuleServiceProvider.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['providers'],
            ),
        ];
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanRouteEntries(string $modulesRoot, string $basePath, string $routeType, array $priorityModules): array
    {
        $filename = self::normalizeRouteType($routeType).'.php';

        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanRouteSupportEntries(string $modulesRoot, string $basePath, string $filename, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanNamedDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanNamedFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                filename: $filename,
            ),
        );
    }

    /**
     * @return list<string>
     */
    private static function discoverNamedFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $entries = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getFilename() !== $filename) {
                continue;
            }

            if ($file->getPathInfo()->getFilename() !== $directoryName) {
                continue;
            }

            $entries[] = self::relativePath($basePath, $file->getRealPath() ?: $file->getPathname());
        }

        $entries = array_values(array_unique($entries));
        sort($entries, SORT_STRING);

        return $entries;
    }

    /**
     * @return list<string>
     */
    private static function discoverNamedDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $entries = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isDir()) {
                continue;
            }

            if ($file->getFilename() !== $directoryName) {
                continue;
            }

            $entries[] = self::relativePath($basePath, $file->getRealPath() ?: $file->getPathname());
        }

        $entries = array_values(array_unique($entries));
        sort($entries, SORT_STRING);

        return $entries;
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function priorityFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename, array $priorityModules): array
    {
        $entries = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $path = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.$directoryName.DIRECTORY_SEPARATOR.$filename;

            if (is_file($path)) {
                $entries[] = self::relativePath($basePath, realpath($path) ?: $path);
            }
        }

        return array_values(array_unique($entries));
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function priorityDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName, array $priorityModules): array
    {
        $entries = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $path = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.$directoryName;

            if (is_dir($path)) {
                $entries[] = self::relativePath($basePath, realpath($path) ?: $path);
            }
        }

        return array_values(array_unique($entries));
    }

    /**
     * @param  list<string>  $priorityEntries
     * @param  list<string>  $discoveredEntries
     * @return list<string>
     */
    private static function mergePrioritizedEntries(array $priorityEntries, array $discoveredEntries): array
    {
        $priorityEntries = array_values(array_unique($priorityEntries));
        $priorityLookup = array_fill_keys($priorityEntries, true);

        $remainingEntries = array_values(array_filter(
            $discoveredEntries,
            static fn (string $entry): bool => ! isset($priorityLookup[$entry]),
        ));

        return [...$priorityEntries, ...$remainingEntries];
    }

    private static function relativePath(string $basePath, string $absolutePath): string
    {
        $normalizedBasePath = str_replace('\\', '/', self::normalizeBasePath($basePath));
        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalizedAbsolutePath, $normalizedBasePath.'/')) {
            return mb_substr($normalizedAbsolutePath, mb_strlen($normalizedBasePath) + 1);
        }

        return $normalizedAbsolutePath;
    }

    /**
     * @return class-string<ServiceProvider>|null
     */
    private static function providerClassFromPath(string $basePath, string $providerFile): ?string
    {
        $relativePath = str_replace('\\', '/', self::relativePath($basePath, $providerFile));

        if (! str_starts_with($relativePath, 'app/') || ! str_ends_with($relativePath, '.php')) {
            return null;
        }

        $classPath = mb_substr($relativePath, 4, -4);

        if ($classPath === '') {
            return null;
        }

        $providerClass = 'App\\'.str_replace('/', '\\', $classPath);

        if (! class_exists($providerClass) || ! is_subclass_of($providerClass, ServiceProvider::class)) {
            return null;
        }

        /** @var class-string<ServiceProvider> $providerClass */
        return $providerClass;
    }

    /**
     * @param  list<string>  $entries
     * @return list<string>
     */
    private static function resolveEntries(string $basePath, array $entries): array
    {
        $normalizedBasePath = self::normalizeBasePath($basePath);

        return array_map(static function (string $entry) use ($normalizedBasePath): string {
            if (preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/|\\\\)/', $entry) === 1) {
                return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $entry);
            }

            $normalizedEntry = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $entry);

            return $normalizedBasePath.DIRECTORY_SEPARATOR.$normalizedEntry;
        }, $entries);
    }

    /**
     * @param  array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * } $payload
     * @return array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function resolvePayloadEntries(string $basePath, array $payload): array
    {
        return [
            'routes' => [
                'web' => self::resolveEntries($basePath, $payload['routes']['web']),
                'api' => self::resolveEntries($basePath, $payload['routes']['api']),
            ],
            'gates' => self::resolveEntries($basePath, $payload['gates']),
            'channels' => self::resolveEntries($basePath, $payload['channels']),
            'listeners' => self::resolveEntries($basePath, $payload['listeners']),
            'providers' => self::resolveEntries($basePath, $payload['providers']),
        ];
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }|null
     */
    private static function loadCachedPayload(string $basePath): ?array
    {
        $cachePath = self::cachePath($basePath);

        if (! is_file($cachePath)) {
            return null;
        }

        $payload = require $cachePath;

        if (! is_array($payload)) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        $normalizedPayload = self::normalizePayload($payload);

        if ($normalizedPayload['version'] !== self::CACHE_VERSION) {
            return null;
        }

        return $normalizedPayload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function normalizePayload(array $payload): array
    {
        $routes = $payload['routes'] ?? [];

        if (! is_array($routes)) {
            $routes = [];
        }

        return [
            'version' => is_int($payload['version'] ?? null)
                ? $payload['version']
                : 0,
            'routes' => [
                'web' => self::normalizeEntryList($routes['web'] ?? []),
                'api' => self::normalizeEntryList($routes['api'] ?? []),
            ],
            'gates' => self::normalizeEntryList($payload['gates'] ?? []),
            'channels' => self::normalizeEntryList($payload['channels'] ?? []),
            'listeners' => self::normalizeEntryList($payload['listeners'] ?? []),
            'providers' => self::normalizeEntryList($payload['providers'] ?? []),
        ];
    }

    /**
     * @return list<string>
     */
    private static function normalizeEntryList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $entries = array_values(array_filter(
            array_map(
                static fn (mixed $entry): ?string => is_string($entry) && $entry !== ''
                    ? str_replace('\\', '/', $entry)
                    : null,
                $value,
            ),
            static fn (?string $entry): bool => $entry !== null,
        ));

        return array_values(array_unique($entries));
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function emptyPayload(): array
    {
        return [
            'version' => self::CACHE_VERSION,
            'routes' => [
                'web' => [],
                'api' => [],
            ],
            'gates' => [],
            'channels' => [],
            'listeners' => [],
            'providers' => [],
        ];
    }
}
