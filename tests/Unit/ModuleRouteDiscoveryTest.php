<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Shared\Support\ModuleRouteDiscovery;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class ModuleRouteDiscoveryTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $temporaryBasePaths = [];

    protected function tearDown(): void
    {
        $filesystem = app(Filesystem::class);

        foreach ($this->temporaryBasePaths as $temporaryBasePath) {
            if ($filesystem->isDirectory($temporaryBasePath)) {
                $filesystem->deleteDirectory($temporaryBasePath);
            }
        }

        parent::tearDown();
    }

    public function test_discovery_prioritizes_canonical_web_modules_then_appends_sorted_discovered_modules(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createRouteFile($basePath, 'Marketing', 'web');
        $this->createRouteFile($basePath, 'Auth', 'web');
        $this->createRouteFile($basePath, 'Users', 'web');
        $this->createRouteFile($basePath, 'Billing', 'web');

        $discovered = ModuleRouteDiscovery::discover(
            basePath: $basePath,
            routeType: 'web',
            priorityModules: ['Marketing', 'Auth', 'Users'],
        );

        $this->assertSame([
            'app/Modules/Marketing/Routes/web.php',
            'app/Modules/Auth/Routes/web.php',
            'app/Modules/Users/Routes/web.php',
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, $discovered));
    }

    public function test_discovery_prioritizes_nested_api_modules_with_stable_fallback_order(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createRouteFile($basePath, 'Api/V1', 'api');
        $this->createRouteFile($basePath, 'Api/V2', 'api');

        $discovered = ModuleRouteDiscovery::discover(
            basePath: $basePath,
            routeType: 'api',
            priorityModules: ['Api/V1'],
        );

        $this->assertSame([
            'app/Modules/Api/V1/Routes/api.php',
            'app/Modules/Api/V2/Routes/api.php',
        ], $this->toRelativePaths($basePath, $discovered));
    }

    public function test_discovery_rejects_invalid_route_types(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->expectException(InvalidArgumentException::class);
        ModuleRouteDiscovery::discover($basePath, 'graphql');
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-route-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);

        return $basePath;
    }

    private function createRouteFile(string $basePath, string $modulePath, string $routeType): void
    {
        $filesystem = app(Filesystem::class);
        $moduleDirectory = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/Routes';

        if (! $filesystem->isDirectory($moduleDirectory)) {
            $filesystem->makeDirectory($moduleDirectory, 0755, true);
        }

        $filesystem->put($moduleDirectory.'/'.$routeType.'.php', '<?php');
    }

    /**
     * @param  list<string>  $absolutePaths
     * @return list<string>
     */
    private function toRelativePaths(string $basePath, array $absolutePaths): array
    {
        $normalizedBasePath = str_replace('\\', '/', mb_rtrim($basePath, '\\/'));

        return array_map(
            static fn (string $absolutePath): string => mb_ltrim(str_replace(
                $normalizedBasePath,
                '',
                str_replace('\\', '/', $absolutePath),
            ), '/'),
            $absolutePaths,
        );
    }
}
