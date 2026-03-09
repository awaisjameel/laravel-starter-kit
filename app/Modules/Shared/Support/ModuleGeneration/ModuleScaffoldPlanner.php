<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use App\Enums\UserRole;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class ModuleScaffoldPlanner
{
    public function __construct(
        private Filesystem $filesystem,
        private TemplateRenderer $templateRenderer,
    ) {}

    public function plan(GenerateModuleInput $generateModuleInput): ModuleScaffoldPlan
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $moduleExists = $this->filesystem->isDirectory($moduleRootPath);

        if ($moduleExists && ! $generateModuleInput->extend) {
            throw new RuntimeException(
                sprintf(
                    'Module already exists at "%s". Use --extend to add page scaffolding to an existing module.',
                    $this->relativePath($generateModuleInput->basePath, $moduleRootPath),
                ),
            );
        }

        if (! $moduleExists && $generateModuleInput->extend) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not exist. Run the command without --extend to scaffold a new module first.',
                    $generateModuleInput->moduleName->namespace,
                ),
            );
        }

        $directories = [];
        $files = [];

        if ($generateModuleInput->generateCrud || $generateModuleInput->generateApi) {
            $this->appendSharedBackendSupportScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generateCrud) {
            $this->appendCrudBackendScaffold($generateModuleInput, $directories, $files);

            if ($this->shouldGenerateRoleGateFile($generateModuleInput)) {
                $this->appendCrudRoleGateScaffold($generateModuleInput, $directories, $files);
            }

            $this->appendFeatureTestScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generateApi) {
            $this->appendApiBackendScaffold($generateModuleInput, $directories, $files);

            if ($generateModuleInput->generateApiFeatureTest) {
                $this->appendApiFeatureTestScaffold($generateModuleInput, $directories, $files);
            }
        }

        if ($generateModuleInput->generateModel) {
            $this->appendModelAndMigrationScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generatePage) {
            $this->appendFrontendPageScaffold($generateModuleInput, $directories, $files);
        }

        $directories = array_values(array_unique($directories));
        sort($directories, SORT_STRING);
        $files = $this->uniquePlannedFiles($files);

        usort(
            $files,
            static fn (PlannedFile $left, PlannedFile $right): int => $left->path <=> $right->path,
        );

        return new ModuleScaffoldPlan(
            moduleExists: $moduleExists,
            directories: $directories,
            files: $files,
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendSharedBackendSupportScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $requestsPath = $moduleRootPath.'/Http/Requests';
        $dataPath = $moduleRootPath.'/Data';
        $queriesPath = $moduleRootPath.'/Queries';
        $commandsPath = $moduleRootPath.'/Commands';
        $handlersPath = $moduleRootPath.'/Handlers';

        $directories[] = $requestsPath;
        $directories[] = $dataPath;
        $directories[] = $queriesPath;
        $directories[] = $commandsPath;

        if ($this->shouldGenerateHandlers($generateModuleInput)) {
            $directories[] = $handlersPath;
        }

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'modelClass' => $this->modelClassName($generateModuleInput->moduleName),
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sStoreRequest.php', $requestsPath, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/request.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sUpdateRequest.php', $requestsPath, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/update-request.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sStoreData.php', $dataPath, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/data.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sQueries.php', $queriesPath, $this->modelClassName($generateModuleInput->moduleName)),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/query.stub'),
                array_merge($tokens, [
                    'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
                ]),
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sCommands.php', $commandsPath, $this->modelClassName($generateModuleInput->moduleName)),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/command.stub'),
                array_merge($tokens, [
                    'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
                ]),
            ),
        );

        if ($this->shouldGenerateHandlers($generateModuleInput)) {
            $handlerTokens = array_merge($tokens, [
                'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
            ]);

            $files[] = new PlannedFile(
                path: sprintf('%s/%sQueryHandler.php', $handlersPath, $this->modelClassName($generateModuleInput->moduleName)),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/query-handler.stub'),
                    $handlerTokens,
                ),
            );

            $files[] = new PlannedFile(
                path: sprintf('%s/%sCommandHandler.php', $handlersPath, $this->modelClassName($generateModuleInput->moduleName)),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/command-handler.stub'),
                    $handlerTokens,
                ),
            );
        }
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendCrudBackendScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $controllersPath = $moduleRootPath.'/Http/Controllers';
        $routesPath = $moduleRootPath.'/Routes';

        $directories[] = $controllersPath;
        $directories[] = $routesPath;

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $moduleKebab = $generateModuleInput->moduleName->frontendKebab;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageCamelName = lcfirst($pagePascalName);
        $routeNamePrefix = $generateModuleInput->routeNamePrefix;
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $modelVariable = $this->modelVariableName($generateModuleInput->moduleName);

        $controllerTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'moduleKebab' => $moduleKebab,
            'routeStoreName' => $routeNamePrefix.'.store',
            'routeIndexName' => $routeNamePrefix.'.index',
            'pageTitle' => $pagePascalName,
            'modelClass' => $modelClass,
            'modelVariable' => $modelVariable,
        ];

        $routeTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'routePrefix' => $generateModuleInput->routePrefix,
            'routeNamePrefix' => $routeNamePrefix,
            'middlewareLine' => $this->buildMiddlewareLine($generateModuleInput->middleware),
            'modelRouteParameter' => '{'.$modelVariable.'}',
        ];

        $controllerStubPath = $this->shouldGenerateHandlers($generateModuleInput)
            ? base_path('stubs/module-generation/backend/controller-with-handlers.stub')
            : base_path('stubs/module-generation/backend/controller.stub');

        $files[] = new PlannedFile(
            path: sprintf('%s/%sController.php', $controllersPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                $controllerStubPath,
                $controllerTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: $routesPath.'/web.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/web-routes.stub'),
                $routeTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendCrudRoleGateScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $routesPath = $moduleRootPath.'/Routes';

        $directories[] = $routesPath;

        $abilityName = 'manage-'.$generateModuleInput->moduleName->frontendKebab;
        $allowedRoleCases = $this->allowedPhpRoleCases($generateModuleInput->allowedRoles);

        $tokens = [
            'abilityName' => $abilityName,
            'allowedRoleCases' => implode(', ', $allowedRoleCases),
        ];

        $files[] = new PlannedFile(
            path: $routesPath.'/gates.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/gates.stub'),
                $tokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendApiBackendScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $controllersPath = $moduleRootPath.'/Http/Controllers';
        $routesPath = $moduleRootPath.'/Routes';
        $resourcesPath = $moduleRootPath.'/Http/Resources';

        $directories[] = $controllersPath;
        $directories[] = $routesPath;

        if ($generateModuleInput->generateApiResource) {
            $directories[] = $resourcesPath;
        }

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageCamelName = lcfirst($pagePascalName);
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $modelVariable = $this->modelVariableName($generateModuleInput->moduleName);

        $controllerTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'modelClass' => $modelClass,
            'modelVariable' => $modelVariable,
        ];

        $routeTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'apiRoutePrefix' => $generateModuleInput->apiRoutePrefix,
            'apiRouteNamePrefix' => $generateModuleInput->apiRouteNamePrefix,
            'apiMiddlewareLine' => $this->buildMiddlewareLine($generateModuleInput->apiMiddleware),
            'modelRouteParameter' => '{'.$modelVariable.'}',
        ];

        $resourceTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'modelClass' => $modelClass,
        ];

        $apiControllerStubPath = match (true) {
            $this->shouldGenerateHandlers($generateModuleInput) && $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller-with-handlers.stub'),
            $this->shouldGenerateHandlers($generateModuleInput) && ! $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller-no-resource-with-handlers.stub'),
            $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller.stub'),
            default => base_path('stubs/module-generation/backend/api-controller-no-resource.stub'),
        };

        $files[] = new PlannedFile(
            path: sprintf('%s/%sApiController.php', $controllersPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                $apiControllerStubPath,
                $controllerTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: $routesPath.'/api.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/api-routes.stub'),
                $routeTokens,
            ),
        );

        if ($generateModuleInput->generateApiResource) {
            $files[] = new PlannedFile(
                path: sprintf('%s/%sResource.php', $resourcesPath, $pagePascalName),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/resource.stub'),
                    $resourceTokens,
                ),
            );
        }
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendModelAndMigrationScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $modelsPath = $generateModuleInput->basePath.'/app/Models';
        $migrationsPath = $generateModuleInput->basePath.'/database/migrations';

        $directories[] = $modelsPath;
        $directories[] = $migrationsPath;

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $tableName = Str::snake(Str::pluralStudly($modelClass));

        $modelTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'modelClass' => $modelClass,
            'tableName' => $tableName,
        ];

        $migrationTokens = [
            'tableName' => $tableName,
        ];

        $migrationFileName = sprintf(
            '%s_create_%s_table.php',
            date('Y_m_d_His'),
            $tableName,
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.php', $modelsPath, $modelClass),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/model.stub'),
                $modelTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s', $migrationsPath, $migrationFileName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/migration.stub'),
                $migrationTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendPageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        if ($generateModuleInput->generateCrud) {
            $this->appendFrontendCrudPageScaffold($generateModuleInput, $directories, $files);

            return;
        }

        $this->appendFrontendSimplePageScaffold($generateModuleInput, $directories, $files);
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendSimplePageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleFrontendRootPath = $this->moduleFrontendRootPath($generateModuleInput);
        $formsPath = $moduleFrontendRootPath.'/forms';
        $pagesPath = $moduleFrontendRootPath.'/pages';
        $pageTestsPath = $pagesPath.'/__tests__';

        $directories[] = $formsPath;
        $directories[] = $pagesPath;
        $directories[] = $pageTestsPath;

        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageKebabName = $generateModuleInput->pageKebabName;
        $pageCamelName = lcfirst($pagePascalName);

        $schemaTokens = [
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
        ];

        $pageTokens = [
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'pageKebabName' => $pageKebabName,
        ];

        $pageTestTokens = [
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-form-schema.ts', $formsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/form-schema.stub'),
                $schemaTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.vue', $pagesPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page.stub'),
                $pageTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.test.ts', $pageTestsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page-test.stub'),
                $pageTestTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendCrudPageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleFrontendRootPath = $this->moduleFrontendRootPath($generateModuleInput);
        $contractsPath = $moduleFrontendRootPath.'/contracts';
        $formsPath = $moduleFrontendRootPath.'/forms';
        $componentsPath = $moduleFrontendRootPath.'/components';
        $pagesPath = $moduleFrontendRootPath.'/pages';
        $pageTestsPath = $pagesPath.'/__tests__';

        $directories[] = $contractsPath;
        $directories[] = $formsPath;
        $directories[] = $componentsPath;
        $directories[] = $pagesPath;
        $directories[] = $pageTestsPath;

        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageKebabName = $generateModuleInput->pageKebabName;
        $moduleComponentPrefix = implode('', $generateModuleInput->moduleName->namespaceSegments);
        $controllerActionImportPath = sprintf(
            '@/actions/App/Modules/%s/Http/Controllers/%sController',
            $generateModuleInput->moduleName->path,
            $pagePascalName,
        );

        $commonTokens = [
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
            'moduleComponentPrefix' => $moduleComponentPrefix,
            'controllerActionImportPath' => $controllerActionImportPath,
            'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
            'moduleKebab' => $generateModuleInput->moduleName->frontendKebab,
            'moduleNavigationTitle' => $this->moduleNavigationTitle($generateModuleInput->moduleName->frontendKebab),
        ];

        $schemaTokens = [
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => lcfirst($pagePascalName),
        ];

        $pageTestTokens = [
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-crud.ts', $contractsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-contract.stub'),
                $commonTokens,
            ),
        );

        if ($generateModuleInput->routeProfile === RouteProfile::APP) {
            $dashboardNavTokens = array_merge($commonTokens, [
                'dashboardNavRoleImportLine' => $generateModuleInput->allowedRoles === []
                    ? ''
                    : "import { UserRole } from '@/types/app-data'\n",
                'dashboardNavRoles' => $this->dashboardNavRolesExpression($generateModuleInput->allowedRoles),
            ]);

            $files[] = new PlannedFile(
                path: sprintf('%s/dashboard-nav.ts', $contractsPath),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/frontend/dashboard-nav.stub'),
                    $dashboardNavTokens,
                ),
            );
        }

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-form-schema.ts', $formsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/form-schema.stub'),
                $schemaTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/Table.vue', $componentsPath),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-table.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sFormDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-form-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sDeleteDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-delete-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sDetailsDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-details-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.vue', $pagesPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-page.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.test.ts', $pageTestsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page-test.stub'),
                $pageTestTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFeatureTestScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $testsDirectory = $generateModuleInput->basePath.'/tests/Feature/'.$generateModuleInput->moduleName->path;
        $directories[] = $testsDirectory;

        $routeUri = $generateModuleInput->routePrefix === '' ? '/' : '/'.$generateModuleInput->routePrefix;
        $routeLabel = str_replace('-', ' ', $generateModuleInput->moduleName->frontendKebab);
        $routeTestName = str_replace(' ', '_', $routeLabel);
        $guestAssertion = in_array('auth', $generateModuleInput->middleware, true)
            ? "\$testResponse->assertRedirect('/auth/login');"
            : '$testResponse->assertOk();';
        $restrictedByRoles = $this->isRoleRestricted($generateModuleInput);
        $deniedRoleCase = $restrictedByRoles ? $this->deniedRoleCase($generateModuleInput->allowedRoles) : null;

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'moduleKebab' => $generateModuleInput->moduleName->frontendKebab,
            'routeUri' => $routeUri,
            'routeLabel' => $routeLabel,
            'routeTestName' => $routeTestName,
            'guestAssertion' => $guestAssertion,
            'userRoleImportLine' => $restrictedByRoles ? "use App\\Enums\\UserRole;\n" : '',
            'forbiddenRoleTest' => $deniedRoleCase !== null
                ? $this->forbiddenRoleTest($routeLabel, $routeUri, $deniedRoleCase)
                : '',
            'allowedRoleFactoryState' => $this->allowedRoleFactoryState($generateModuleInput->allowedRoles),
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sPageTest.php', $testsDirectory, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/tests/feature-page-test.stub'),
                $tokens,
            ),
        );
    }

    private function shouldGenerateRoleGateFile(GenerateModuleInput $generateModuleInput): bool
    {
        return $generateModuleInput->routeProfile === RouteProfile::APP
            && $this->isRoleRestricted($generateModuleInput);
    }

    private function isRoleRestricted(GenerateModuleInput $generateModuleInput): bool
    {
        return $generateModuleInput->allowedRoles !== [];
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendApiFeatureTestScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $testsDirectory = $generateModuleInput->basePath.'/tests/Feature/'.$generateModuleInput->moduleName->path;
        $directories[] = $testsDirectory;

        $apiRouteUri = $generateModuleInput->apiRoutePrefix === '' ? '/' : '/'.$generateModuleInput->apiRoutePrefix;
        $routeLabel = str_replace('-', ' ', $generateModuleInput->moduleName->frontendKebab);
        $routeTestName = str_replace(' ', '_', $routeLabel);
        $apiRequiresAuth = in_array('auth:sanctum', $generateModuleInput->apiMiddleware, true);

        $apiGuestAssertion = $apiRequiresAuth
            ? '$testResponse->assertUnauthorized();'
            : '$testResponse->assertOk();';

        $apiAuthRequestLine = $apiRequiresAuth
            ? "\$user = User::factory()->create();\n\n        \$testResponse = \$this->actingAs(\$user)->getJson('{$apiRouteUri}');"
            : sprintf("\$testResponse = \$this->getJson('%s');", $apiRouteUri);

        $apiAuthFollowUpLine = $apiRequiresAuth
            ? sprintf("\$this->actingAs(\$user)->getJson('%s')", $apiRouteUri)
            : sprintf("\$this->getJson('%s')", $apiRouteUri);

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'modelClass' => $this->modelClassName($generateModuleInput->moduleName),
            'routeLabel' => $routeLabel,
            'routeTestName' => $routeTestName,
            'apiRouteUri' => $apiRouteUri,
            'apiGuestAssertion' => $apiGuestAssertion,
            'apiAuthRequestLine' => $apiAuthRequestLine,
            'apiAuthFollowUpLine' => $apiAuthFollowUpLine,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sApiTest.php', $testsDirectory, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/tests/feature-api-test.stub'),
                $tokens,
            ),
        );
    }

    /**
     * @param  array<int|string, string>  $middleware
     */
    private function buildMiddlewareLine(array $middleware): string
    {
        if ($middleware === []) {
            return '';
        }

        $quoted = array_map(
            static fn (string $middlewareItem): string => sprintf("'%s'", $middlewareItem),
            $middleware,
        );

        return sprintf("    ->middleware([%s])\n", implode(', ', $quoted));
    }

    private function shouldGenerateHandlers(GenerateModuleInput $generateModuleInput): bool
    {
        return $generateModuleInput->generateCrud && $generateModuleInput->generateApi;
    }

    private function moduleRootPath(GenerateModuleInput $generateModuleInput): string
    {
        return $generateModuleInput->basePath.'/app/Modules/'.$generateModuleInput->moduleName->path;
    }

    private function moduleFrontendRootPath(GenerateModuleInput $generateModuleInput): string
    {
        return $generateModuleInput->basePath.'/resources/js/modules/'.$generateModuleInput->moduleName->frontendKebab;
    }

    private function modelClassName(ModuleName $moduleName): string
    {
        return implode('', $moduleName->namespaceSegments);
    }

    private function modelVariableName(ModuleName $moduleName): string
    {
        return Str::camel($this->modelClassName($moduleName));
    }

    private function relativePath(string $basePath, string $absolutePath): string
    {
        $normalizedBasePath = str_replace('\\', '/', mb_rtrim($basePath, '\\/'));
        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalizedAbsolutePath, $normalizedBasePath.'/')) {
            return mb_substr($normalizedAbsolutePath, mb_strlen($normalizedBasePath) + 1);
        }

        return $normalizedAbsolutePath;
    }

    /**
     * @param  array<int, PlannedFile>  $files
     * @return list<PlannedFile>
     */
    private function uniquePlannedFiles(array $files): array
    {
        $uniqueFiles = [];

        foreach ($files as $file) {
            $uniqueFiles[$file->path] = $file;
        }

        return array_values($uniqueFiles);
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function allowedPhpRoleCases(array $allowedRoles): array
    {
        $roleCaseLookup = [];

        foreach (UserRole::cases() as $role) {
            $roleCaseLookup[$role->value] = 'UserRole::'.$role->name;
        }

        $cases = [];

        foreach ($allowedRoles as $allowedRole) {
            if (isset($roleCaseLookup[$allowedRole])) {
                $cases[] = $roleCaseLookup[$allowedRole];
            }
        }

        return $cases;
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function allowedTsRoleCases(array $allowedRoles): array
    {
        $roleCaseLookup = [];

        foreach (UserRole::cases() as $role) {
            $roleCaseLookup[$role->value] = 'UserRole.'.$role->name;
        }

        $cases = [];

        foreach ($allowedRoles as $allowedRole) {
            if (isset($roleCaseLookup[$allowedRole])) {
                $cases[] = $roleCaseLookup[$allowedRole];
            }
        }

        return $cases;
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function dashboardNavRolesExpression(array $allowedRoles): string
    {
        if ($allowedRoles === []) {
            return "'all'";
        }

        return '['.implode(', ', $this->allowedTsRoleCases($allowedRoles)).']';
    }

    private function moduleNavigationTitle(string $moduleKebab): string
    {
        return Str::title(str_replace('-', ' ', $moduleKebab));
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function allowedRoleFactoryState(array $allowedRoles): string
    {
        if ($allowedRoles === []) {
            return '';
        }

        $firstAllowedRoleCase = $this->allowedPhpRoleCases($allowedRoles)[0] ?? null;

        if ($firstAllowedRoleCase === null) {
            return '';
        }

        return sprintf("['role' => %s]", $firstAllowedRoleCase);
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function deniedRoleCase(array $allowedRoles): ?string
    {
        $allowedRoleLookup = array_fill_keys($allowedRoles, true);

        foreach (UserRole::cases() as $role) {
            if (! isset($allowedRoleLookup[$role->value])) {
                return 'UserRole::'.$role->name;
            }
        }

        return null;
    }

    private function forbiddenRoleTest(string $routeLabel, string $routeUri, string $deniedRoleCase): string
    {
        return sprintf(
            "    public function test_users_without_required_role_cannot_visit_%s_page(): void\n".
            "    {\n".
            "        \$user = User::factory()->create(['role' => %s]);\n\n".
            "        \$testResponse = \$this->actingAs(\$user)->get('%s');\n\n".
            "        \$testResponse->assertForbidden();\n".
            "    }\n",
            str_replace(' ', '_', $routeLabel),
            $deniedRoleCase,
            $routeUri,
        );
    }
}
