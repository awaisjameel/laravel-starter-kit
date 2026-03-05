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

        $replacements = [];

        foreach ($tokens as $key => $value) {
            $replacements[sprintf('{{ %s }}', $key)] = $value;
            $replacements[sprintf('{{%s}}', $key)] = $value;
        }

        return strtr($contents, $replacements);
    }
}
