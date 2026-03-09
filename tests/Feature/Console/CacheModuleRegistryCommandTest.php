<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CacheModuleRegistryCommandTest extends TestCase
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

    public function test_command_writes_cached_module_manifest(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createModuleFile($basePath, 'Marketing', 'Routes/web.php');
        $this->createModuleFile($basePath, 'Users', 'Routes/gates.php');
        $this->createModuleFile($basePath, 'Users', 'Policies/UserPolicy.php');
        $this->createModuleFile($basePath, 'Shared', 'Routes/channels.php');
        $this->createModuleDirectory($basePath, 'Users', 'Listeners');
        $this->createModuleFile($basePath, 'Users', 'Providers/ModuleServiceProvider.php');

        $exitCode = app(Kernel::class)->call('modules:cache', [
            '--base-path' => $basePath,
        ]);

        $this->assertSame(0, $exitCode);

        $cachePath = ModuleRegistry::cachePath($basePath);

        $this->assertFileExists($cachePath);

        /** @var array<string, mixed> $payload */
        $payload = require $cachePath;
        $routes = $payload['routes'] ?? [];

        if (! is_array($routes)) {
            $routes = [];
        }

        $this->assertSame([
            'app/Modules/Marketing/Routes/web.php',
        ], $routes['web'] ?? []);

        $this->assertSame([
            'app/Modules/Users/Routes/gates.php',
        ], $payload['gates'] ?? []);

        $this->assertSame([
            'app/Modules/Users/Policies/UserPolicy.php',
        ], $payload['policies'] ?? []);

        $this->assertSame([
            'app/Modules/Shared/Routes/channels.php',
        ], $payload['channels'] ?? []);

        $this->assertSame([
            'app/Modules/Users/Listeners',
        ], $payload['listeners'] ?? []);

        $this->assertSame([
            'app/Modules/Users/Providers/ModuleServiceProvider.php',
        ], $payload['providers'] ?? []);
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-cache-command-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);

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
}
