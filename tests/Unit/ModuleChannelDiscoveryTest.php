<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Shared\Support\ModuleChannelDiscovery;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ModuleChannelDiscoveryTest extends TestCase
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

    public function test_discovery_prioritizes_modules_then_appends_sorted_discovered_channel_files(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->createChannelFile($basePath, 'Users');
        $this->createChannelFile($basePath, 'Shared');
        $this->createChannelFile($basePath, 'Billing');

        $discovered = ModuleChannelDiscovery::discover(
            basePath: $basePath,
            priorityModules: ['Shared', 'Users'],
        );

        $this->assertSame([
            'app/Modules/Shared/Routes/channels.php',
            'app/Modules/Users/Routes/channels.php',
            'app/Modules/Billing/Routes/channels.php',
        ], $this->toRelativePaths($basePath, $discovered));
    }

    public function test_discovery_returns_empty_when_modules_directory_is_missing(): void
    {
        $basePath = storage_path('framework/testing/module-channel-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $discovered = ModuleChannelDiscovery::discover($basePath);

        $this->assertSame([], $discovered);
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/module-channel-discovery-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $filesystem = app(Filesystem::class);
        $filesystem->makeDirectory($basePath.'/app/Modules', 0755, true);

        return $basePath;
    }

    private function createChannelFile(string $basePath, string $modulePath): void
    {
        $filesystem = app(Filesystem::class);
        $moduleDirectory = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/Routes';

        if (! $filesystem->isDirectory($moduleDirectory)) {
            $filesystem->makeDirectory($moduleDirectory, 0755, true);
        }

        $filesystem->put($moduleDirectory.'/channels.php', '<?php');
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
