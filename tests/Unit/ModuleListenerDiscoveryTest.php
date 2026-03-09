<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Shared\Support\ModuleListenerDiscovery;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ModuleListenerDiscoveryTest extends TestCase
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

    public function test_discovery_prioritizes_modules_then_appends_sorted_listener_directories(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createListenerDirectory($basePath, 'Users');
        $this->createListenerDirectory($basePath, 'Billing');
        $this->createListenerDirectory($basePath, 'Api/V1');

        $discovered = ModuleListenerDiscovery::discoverDirectories(
            basePath: $basePath,
            priorityModules: ['Users'],
        );

        $this->assertSame([
            'app/Modules/Users/Listeners',
            'app/Modules/Api/V1/Listeners',
            'app/Modules/Billing/Listeners',
        ], $this->toRelativePaths($basePath, $discovered));
    }

    public function test_discovery_returns_empty_when_modules_directory_is_missing(): void
    {
        $basePath = storage_path('framework/testing/module-listener-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $discovered = ModuleListenerDiscovery::discoverDirectories($basePath);

        $this->assertSame([], $discovered);
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-listener-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);

        return $basePath;
    }

    private function createListenerDirectory(string $basePath, string $modulePath): void
    {
        $filesystem = app(Filesystem::class);
        $moduleDirectory = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/Listeners';

        if (! $filesystem->isDirectory($moduleDirectory)) {
            $filesystem->makeDirectory($moduleDirectory, 0755, true);
        }
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
