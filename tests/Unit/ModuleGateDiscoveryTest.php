<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Shared\Support\ModuleGateDiscovery;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ModuleGateDiscoveryTest extends TestCase
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

    public function test_discovery_prioritizes_modules_then_appends_sorted_discovered_gate_files(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createGateFile($basePath, 'Users');
        $this->createGateFile($basePath, 'Billing');
        $this->createGateFile($basePath, 'Api/V1');

        $discovered = ModuleGateDiscovery::discover(
            basePath: $basePath,
            priorityModules: ['Users'],
        );

        $this->assertSame([
            'app/Modules/Users/Routes/gates.php',
            'app/Modules/Api/V1/Routes/gates.php',
            'app/Modules/Billing/Routes/gates.php',
        ], $this->toRelativePaths($basePath, $discovered));
    }

    public function test_discovery_returns_empty_when_modules_directory_is_missing(): void
    {
        $basePath = storage_path('framework/testing/module-gate-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $discovered = ModuleGateDiscovery::discover($basePath);

        $this->assertSame([], $discovered);
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-gate-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);

        return $basePath;
    }

    private function createGateFile(string $basePath, string $modulePath): void
    {
        $filesystem = app(Filesystem::class);
        $moduleDirectory = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/Routes';

        if (! $filesystem->isDirectory($moduleDirectory)) {
            $filesystem->makeDirectory($moduleDirectory, 0755, true);
        }

        $filesystem->put($moduleDirectory.'/gates.php', '<?php');
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
