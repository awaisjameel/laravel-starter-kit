<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

test('wayfinder writes portable actions routes and form helpers', function (): void {
    $path = $this->temporaryDirectoryPath('wayfinder');

    expect(Artisan::call('wayfinder:generate', ['--path' => $path, '--with-form' => true]))->toBe(0);

    $filesystem = app(Filesystem::class);

    foreach ($filesystem->allFiles($path) as $file) {
        expect($file->getContents())->not->toContain("\r");
    }

    $actions = $filesystem->get($path.'/actions/App/Modules/Users/Http/Controllers/UserController.ts');
    $routes = $filesystem->get($path.'/routes/app/admin/users/index.ts');

    foreach ([$actions, $routes] as $source) {
        expect($source)
            ->toContain("url: '/app/admin/users/{user}'")
            ->toContain('update.form')
            ->toContain("\n    if (Array.isArray(args)) {\n        args = {\n            user: args[0],\n        }\n    }");
    }

    expect($filesystem->get($path.'/wayfinder/index.ts'))->toContain('export type RouteFormDefinition');
    expect($filesystem->get($path.'/routes/app/index.ts'))->toContain("\n    settings: Object.assign(settings, settings),");
});
