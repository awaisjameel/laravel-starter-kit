<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final readonly class TemplateRenderer
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    /**
     * @param  array<string, string>  $tokens
     */
    public function render(string $stubPath, array $tokens): string
    {
        if (! $this->filesystem->exists($stubPath)) {
            throw new RuntimeException('Stub file not found: '.$stubPath);
        }

        $contents = $this->filesystem->get($stubPath);

        $rendered = preg_replace_callback('/{{\s*([A-Za-z][A-Za-z0-9]*)\s*}}/', static function (array $match) use ($tokens, $stubPath): string {
            $key = $match[1];

            return $tokens[$key] ?? throw new RuntimeException(sprintf('Missing template token "%s" in %s.', $key, $stubPath));
        }, $contents);

        $rendered ?? throw new RuntimeException('Could not render stub: '.$stubPath);

        return str_starts_with($rendered, '<?php') ? PhpUseStatementSorter::sort($rendered) : $rendered;
    }
}
