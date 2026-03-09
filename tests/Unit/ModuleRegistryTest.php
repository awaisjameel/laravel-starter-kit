<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ModuleRegistryTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $temporaryBasePaths = [];

    protected function tearDown(): void
    {
        $filesystem = app(Filesystem::class);
        ModuleRegistry::flushRuntimeCache();

        foreach ($this->temporaryBasePaths as $temporaryBasePath) {
            if ($filesystem->isDirectory($temporaryBasePath)) {
                $filesystem->deleteDirectory($temporaryBasePath);
            }
        }

        parent::tearDown();
    }

    public function test_registry_discovers_and_prioritizes_all_supported_module_assets(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Marketing', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Auth', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Users', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Api/V1', 'Routes/api.php');
        $this->createModuleFile($basePath, 'Api/V2', 'Routes/api.php');
        $this->createModuleFile($basePath, 'Users', 'Routes/gates.php');
        $this->createModuleFile($basePath, 'Billing', 'Routes/gates.php');
        $this->createModuleFile($basePath, 'Users', 'Policies/UserPolicy.php');
        $this->createModuleFile($basePath, 'Billing', 'Policies/BillingPolicy.php');
        $this->createModuleFile($basePath, 'Shared', 'Routes/channels.php');
        $this->createModuleFile($basePath, 'Users', 'Routes/channels.php');
        $this->createModuleFile($basePath, 'Billing', 'Routes/channels.php');
        $this->createModuleDirectory($basePath, 'Users', 'Listeners');
        $this->createModuleDirectory($basePath, 'Billing', 'Listeners');
        $this->createModuleDirectory($basePath, 'Api/V1', 'Listeners');
        $this->createModuleFile($basePath, 'Users', 'Providers/ModuleServiceProvider.php');
        $this->createModuleFile($basePath, 'Billing', 'Providers/ModuleServiceProvider.php');

        $this->assertSame([
            'app/Modules/Marketing/Routes/web.php',
            'app/Modules/Auth/Routes/web.php',
            'app/Modules/Users/Routes/web.php',
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));

        $this->assertSame([
            'app/Modules/Api/V1/Routes/api.php',
            'app/Modules/Api/V2/Routes/api.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::apiRoutes($basePath)));

        $this->assertSame([
            'app/Modules/Users/Routes/gates.php',
            'app/Modules/Billing/Routes/gates.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)));

        $this->assertSame([
            'app/Modules/Users/Policies/UserPolicy.php',
            'app/Modules/Billing/Policies/BillingPolicy.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::policyFiles($basePath)));

        $this->assertSame([
            'app/Modules/Shared/Routes/channels.php',
            'app/Modules/Users/Routes/channels.php',
            'app/Modules/Billing/Routes/channels.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::channelFiles($basePath)));

        $this->assertSame([
            'app/Modules/Users/Listeners',
            'app/Modules/Api/V1/Listeners',
            'app/Modules/Billing/Listeners',
        ], $this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)));

        $this->assertSame([
            'app/Modules/Billing/Providers/ModuleServiceProvider.php',
            'app/Modules/Users/Providers/ModuleServiceProvider.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::providerFiles($basePath)));

        $this->assertSame([], ModuleRegistry::providerClasses($basePath));
    }

    public function test_registry_uses_cached_manifest_when_available(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Cached', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Cached', 'Routes/gates.php');
        $this->createModuleFile($basePath, 'Cached', 'Policies/CachedPolicy.php');
        $this->createModuleDirectory($basePath, 'Cached', 'Listeners');
        $this->createModuleFile($basePath, 'Ignored', 'Routes/web.php');

        file_put_contents(
            ModuleRegistry::cachePath($basePath),
            <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Cached/Routes/web.php'],
        'api' => [],
    ],
    'gates' => ['app/Modules/Cached/Routes/gates.php'],
    'policies' => ['app/Modules/Cached/Policies/CachedPolicy.php'],
    'channels' => [],
    'listeners' => ['app/Modules/Cached/Listeners'],
    'providers' => [],
];
PHP
        );

        $this->assertSame([
            'app/Modules/Cached/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));

        $this->assertSame([
            'app/Modules/Cached/Routes/gates.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)));

        $this->assertSame([
            'app/Modules/Cached/Policies/CachedPolicy.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::policyFiles($basePath)));

        $this->assertSame([
            'app/Modules/Cached/Listeners',
        ], $this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)));
    }

    public function test_registry_rebuilds_when_cached_manifest_references_deleted_entries(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Billing', 'Routes/gates.php');
        $this->createModuleDirectory($basePath, 'Billing', 'Listeners');

        file_put_contents(
            ModuleRegistry::cachePath($basePath),
            <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Application/Routes/web.php'],
        'api' => [],
    ],
    'gates' => ['app/Modules/Application/Routes/gates.php'],
    'policies' => ['app/Modules/Application/Policies/ApplicationPolicy.php'],
    'channels' => [],
    'listeners' => ['app/Modules/Application/Listeners'],
    'providers' => [],
];
PHP
        );

        $this->assertSame([
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));

        $this->assertSame([
            'app/Modules/Billing/Routes/gates.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)));

        $this->assertSame([], ModuleRegistry::policyFiles($basePath));

        $this->assertSame([
            'app/Modules/Billing/Listeners',
        ], $this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)));
    }

    public function test_registry_resolves_autoloadable_provider_classes_for_the_application(): void
    {
        $this->assertContains(ModuleServiceProvider::class, ModuleRegistry::providerClasses(base_path()));
    }

    public function test_registry_runtime_caches_discovery_results_until_flushed(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');

        $this->assertSame([
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));

        $this->createModuleFile($basePath, 'Reports', 'Routes/web.php');

        $this->assertSame([
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));

        ModuleRegistry::flushRuntimeCache();

        $this->assertSame([
            'app/Modules/Billing/Routes/web.php',
            'app/Modules/Reports/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)));
    }

    public function test_registry_trusts_cached_manifest_for_production_http_runtime(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');

        file_put_contents(
            ModuleRegistry::cachePath($basePath),
            <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Cached/Routes/web.php'],
        'api' => [],
    ],
    'gates' => [],
    'policies' => [],
    'channels' => [],
    'listeners' => [],
    'providers' => [],
];
PHP
        );

        $this->assertSame([
            'app/Modules/Cached/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::discover($basePath, false, 'production')['routes']['web']));

        ModuleRegistry::flushRuntimeCache();

        $this->assertSame([
            'app/Modules/Billing/Routes/web.php',
        ], $this->toRelativePaths($basePath, ModuleRegistry::discover($basePath, true, 'production')['routes']['web']));
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-registry-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);
        $filesystem->makeDirectory($basePath.'/bootstrap/cache', 0755, true);

        return $basePath;
    }

    private function createModuleDirectory(string $basePath, string $modulePath, string $relativePath): void
    {
        $filesystem = app(Filesystem::class);
        $path = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/'.$relativePath;

        if (! $filesystem->isDirectory($path)) {
            $filesystem->makeDirectory($path, 0755, true);
        }
    }

    private function createModuleFile(string $basePath, string $modulePath, string $relativePath): void
    {
        $filesystem = app(Filesystem::class);
        $path = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/'.$relativePath;
        $directory = dirname($path);

        if (! $filesystem->isDirectory($directory)) {
            $filesystem->makeDirectory($directory, 0755, true);
        }

        $filesystem->put($path, '<?php');
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
