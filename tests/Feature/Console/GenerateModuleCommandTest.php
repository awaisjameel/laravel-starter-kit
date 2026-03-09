<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

final class GenerateModuleCommandTest extends TestCase
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

    public function test_fresh_crud_mode_scaffolds_backend_frontend_and_tests(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--page' => 'Index',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Requests/IndexUpdateRequest.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Data/IndexStoreData.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Data/IndexListItemData.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Data/IndexPageData.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Commands/BillingCommands.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Services/IndexService.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Routes/web.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/api.php');
        $this->assertFileExists($basePath.'/app/Models/Billing.php');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/contracts/index-crud.ts');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/forms/index-form-schema.ts');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/components/Table.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/components/IndexFormDialog.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/components/IndexDeleteDialog.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/components/IndexDetailsDialog.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/pages/Index.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/pages/__tests__/Index.test.ts');
        $this->assertFileExists($basePath.'/tests/Feature/Billing/IndexPageTest.php');
        $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/IndexApiTest.php');
        $this->assertMigrationFileExists($basePath, 'billings');

        $requestFileContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php');
        $requestFileContents = is_string($requestFileContents) ? $requestFileContents : '';

        $this->assertStringContainsString('extends DataFormRequest', $requestFileContents);
        $this->assertStringContainsString('return IndexStoreData::class;', $requestFileContents);

        $modelFileContents = file_get_contents($basePath.'/app/Models/Billing.php');
        $modelFileContents = is_string($modelFileContents) ? $modelFileContents : '';
        $this->assertStringContainsString('/** @use HasFactory<Factory<self>> */', $modelFileContents);

        $queryFileContents = file_get_contents($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
        $queryFileContents = is_string($queryFileContents) ? $queryFileContents : '';
        $this->assertStringContainsString('@return LengthAwarePaginator<int, Billing>', $queryFileContents);

        $controllerFileContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
        $controllerFileContents = is_string($controllerFileContents) ? $controllerFileContents : '';
        $this->assertStringContainsString('use App\\Modules\\Billing\\Data\\IndexPageData;', $controllerFileContents);
        $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\PageResponder;', $controllerFileContents);
        $this->assertStringContainsString('PageResponder::render(', $controllerFileContents);
        $this->assertStringContainsString('IndexPageData::fromPaginator($lengthAwarePaginator)', $controllerFileContents);

        $pageDataContents = file_get_contents($basePath.'/app/Modules/Billing/Data/IndexPageData.php');
        $pageDataContents = is_string($pageDataContents) ? $pageDataContents : '';
        $this->assertStringContainsString('final class IndexPageData extends Data', $pageDataContents);
        $this->assertStringContainsString('final class IndexListItemData extends Data', (string) file_get_contents($basePath.'/app/Modules/Billing/Data/IndexListItemData.php'));

        $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
        $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

        $this->assertStringContainsString("Route::prefix('app/billing')", $routeFileContents);
        $this->assertStringContainsString("->as('app.billing.')", $routeFileContents);
        $this->assertStringContainsString("Route::put('/{billing}'", $routeFileContents);
        $this->assertStringNotContainsString('can:manage-billing', $routeFileContents);
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/gates.php');

        $pageFileContents = file_get_contents($basePath.'/resources/js/modules/billing/pages/Index.vue');
        $pageFileContents = is_string($pageFileContents) ? $pageFileContents : '';

        $this->assertStringContainsString("import type { IndexPageData } from '@/types/app-data'", $pageFileContents);
        $this->assertStringContainsString('<BillingTable', $pageFileContents);
        $this->assertStringContainsString('<BillingIndexFormDialog', $pageFileContents);

        $crudContractContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/index-crud.ts');
        $crudContractContents = is_string($crudContractContents) ? $crudContractContents : '';
        $this->assertStringContainsString("import type { IndexListItemData } from '@/types/app-data'", $crudContractContents);
        $this->assertStringContainsString('export type IndexListItem = IndexListItemData', $crudContractContents);

        $tableFileContents = file_get_contents($basePath.'/resources/js/modules/billing/components/Table.vue');
        $tableFileContents = is_string($tableFileContents) ? $tableFileContents : '';
        $this->assertStringContainsString('const rowKey = (row: IndexListItem): number => row.id', $tableFileContents);
        $this->assertStringContainsString(':row-key="rowKey"', $tableFileContents);
    }

    public function test_api_mode_scaffolds_api_assets_and_skips_frontend_assets(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'api',
            '--api-route-profile' => 'protected',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Resources/IndexResource.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Requests/IndexUpdateRequest.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Data/IndexStoreData.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Commands/BillingCommands.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Services/IndexService.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Routes/api.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/web.php');
        $this->assertFileExists($basePath.'/tests/Feature/Billing/IndexApiTest.php');
        $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/IndexPageTest.php');
        $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/Index.vue');
        $this->assertFileExists($basePath.'/app/Models/Billing.php');
        $this->assertMigrationFileExists($basePath, 'billings');

        $apiRouteFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/api.php');
        $apiRouteFileContents = is_string($apiRouteFileContents) ? $apiRouteFileContents : '';

        $this->assertStringContainsString("Route::prefix('api/v1/admin/billing')", $apiRouteFileContents);
        $this->assertStringContainsString("->as('api.v1.admin.billing.')", $apiRouteFileContents);

        $queryFileContents = file_get_contents($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
        $queryFileContents = is_string($queryFileContents) ? $queryFileContents : '';
        $this->assertStringContainsString('@return LengthAwarePaginator<int, Billing>', $queryFileContents);

        $apiControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
        $apiControllerContents = is_string($apiControllerContents) ? $apiControllerContents : '';
        $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\ApiResponder;', $apiControllerContents);
        $this->assertStringContainsString('return ApiResponder::collection(', $apiControllerContents);
        $this->assertStringContainsString('return ApiResponder::resource(IndexResource::make($model), 201);', $apiControllerContents);
    }

    public function test_crud_api_mode_scaffolds_both_backend_route_files(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud-api',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--api-route-profile' => 'protected',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $this->assertFileExists($basePath.'/app/Modules/Billing/Routes/web.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Routes/api.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php');
        $this->assertFileExists($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php');
        $this->assertFileExists($basePath.'/tests/Feature/Billing/IndexPageTest.php');
        $this->assertFileExists($basePath.'/tests/Feature/Billing/IndexApiTest.php');

        $webControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
        $webControllerContents = is_string($webControllerContents) ? $webControllerContents : '';
        $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingQueryHandler;', $webControllerContents);
        $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingCommandHandler;', $webControllerContents);
        $this->assertStringContainsString('private readonly BillingQueryHandler $billingQueryHandler', $webControllerContents);
        $this->assertStringContainsString('private readonly BillingCommandHandler $billingCommandHandler', $webControllerContents);

        $apiControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
        $apiControllerContents = is_string($apiControllerContents) ? $apiControllerContents : '';
        $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingQueryHandler;', $apiControllerContents);
        $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingCommandHandler;', $apiControllerContents);
        $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\ApiResponder;', $apiControllerContents);
        $this->assertStringContainsString('private readonly BillingQueryHandler $billingQueryHandler', $apiControllerContents);
        $this->assertStringContainsString('private readonly BillingCommandHandler $billingCommandHandler', $apiControllerContents);
        $this->assertStringContainsString('use App\\Modules\\Billing\\Data\\IndexPageData;', $webControllerContents);
        $this->assertStringContainsString('PageResponder::render(', $webControllerContents);
    }

    public function test_existing_module_requires_extend_flag(): void
    {
        $basePath = $this->createTemporaryBasePath();
        $this->ensureDirectory($basePath.'/app/Modules/Billing');

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--page' => 'Index',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsOutputToContain('Use --extend')
            ->assertExitCode(1);
    }

    public function test_extend_mode_with_page_scaffold_creates_only_frontend_page_artifacts(): void
    {
        $basePath = $this->createTemporaryBasePath();
        $this->ensureDirectory($basePath.'/app/Modules/Billing');

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--extend' => true,
            '--scaffold' => 'page',
            '--page' => 'InviteUser',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $this->assertFileExists($basePath.'/resources/js/modules/billing/forms/invite-user-form-schema.ts');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/pages/InviteUser.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/pages/__tests__/InviteUser.test.ts');

        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Http/Controllers/InviteUserController.php');
        $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/InviteUserPageTest.php');
        $this->assertFileDoesNotExist($basePath.'/app/Models/Billing.php');
        $this->assertNoMigrationFileExists($basePath, 'billings');
    }

    public function test_dry_run_outputs_plan_without_writing_files(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--page' => 'Index',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--dry-run' => true,
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsOutputToContain('Dry run mode enabled')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
        $this->assertFileDoesNotExist($basePath.'/app/Models/Billing.php');
        $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/Index.vue');
        $this->assertNoMigrationFileExists($basePath, 'billings');
    }

    public function test_existing_target_files_require_force_to_overwrite(): void
    {
        $basePath = $this->createTemporaryBasePath();
        $this->ensureDirectory($basePath.'/resources/js/modules/billing/forms');
        file_put_contents($basePath.'/resources/js/modules/billing/forms/index-form-schema.ts', 'existing');

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--page' => 'Index',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsOutputToContain('Use --force to overwrite')
            ->assertExitCode(1);
    }

    public function test_interactive_file_prompt_allows_selective_generation_for_page_scaffold(): void
    {
        $basePath = $this->createTemporaryBasePath();
        $this->ensureDirectory($basePath.'/app/Modules/Billing');

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--extend' => true,
            '--scaffold' => 'page',
            '--page' => 'InviteUser',
            '--base-path' => $basePath,
        ])
            ->expectsConfirmation('Generate file [resources/js/modules/billing/forms/invite-user-form-schema.ts]?', 'yes')
            ->expectsConfirmation('Generate file [resources/js/modules/billing/pages/InviteUser.vue]?', 'no')
            ->expectsConfirmation('Generate file [resources/js/modules/billing/pages/__tests__/InviteUser.test.ts]?', 'yes')
            ->assertExitCode(0);

        $this->assertFileExists($basePath.'/resources/js/modules/billing/forms/invite-user-form-schema.ts');
        $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/InviteUser.vue');
        $this->assertFileExists($basePath.'/resources/js/modules/billing/pages/__tests__/InviteUser.test.ts');
    }

    public function test_interactive_prompt_branches_for_api_scaffold(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsChoice(
                'Select scaffolding target for the generated module',
                'api',
                ['page', 'crud', 'api', 'crud-api'],
            )
            ->expectsChoice(
                'Select an API route profile for the generated module',
                'protected',
                ['protected', 'public', 'custom'],
            )
            ->assertExitCode(0);

        $this->assertFileExists($basePath.'/app/Modules/Billing/Routes/api.php');
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/web.php');
    }

    public function test_non_interactive_app_crud_requires_roles_option(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--route-profile' => 'app',
            '--no-interaction' => true,
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsOutputToContain('The --roles option is required for app CRUD scaffolding')
            ->assertExitCode(1);
    }

    public function test_non_interactive_app_crud_api_requires_roles_option(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud-api',
            '--route-profile' => 'app',
            '--api-route-profile' => 'protected',
            '--no-interaction' => true,
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsOutputToContain('The --roles option is required for app CRUD scaffolding')
            ->assertExitCode(1);
    }

    public function test_admin_only_roles_generate_admin_route_shape_and_gate_restriction(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--route-profile' => 'app',
            '--roles' => 'admin',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
        $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

        $this->assertStringContainsString("Route::prefix('app/admin/billing')", $routeFileContents);
        $this->assertStringContainsString("->as('app.admin.billing.')", $routeFileContents);
        $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);

        $gateFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/gates.php');
        $gateFileContents = is_string($gateFileContents) ? $gateFileContents : '';

        $this->assertStringContainsString("Gate::define('manage-billing'", $gateFileContents);
        $this->assertStringContainsString('UserRole::Admin', $gateFileContents);

        $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
        $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

        $this->assertStringContainsString('roles: [UserRole.Admin]', $dashboardNavContents);
    }

    public function test_multi_role_scope_keeps_default_app_route_shape_and_generates_role_gate(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--route-profile' => 'app',
            '--roles' => 'admin,user',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
        $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

        $this->assertStringContainsString("Route::prefix('app/billing')", $routeFileContents);
        $this->assertStringContainsString("->as('app.billing.')", $routeFileContents);
        $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);

        $gateFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/gates.php');
        $gateFileContents = is_string($gateFileContents) ? $gateFileContents : '';

        $this->assertStringContainsString('UserRole::Admin', $gateFileContents);
        $this->assertStringContainsString('UserRole::User', $gateFileContents);

        $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
        $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

        $this->assertStringContainsString('roles: [UserRole.Admin, UserRole.User]', $dashboardNavContents);
    }

    public function test_all_roles_scope_skips_role_gate_file_and_role_middleware(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--route-profile' => 'app',
            '--roles' => 'all',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
        $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

        $this->assertStringNotContainsString('can:manage-billing', $routeFileContents);
        $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/gates.php');

        $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
        $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

        $this->assertStringContainsString("roles: 'all'", $dashboardNavContents);
    }

    public function test_interactive_prompt_asks_for_roles_when_scaffolding_app_crud(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'Billing',
            '--scaffold' => 'crud',
            '--route-profile' => 'app',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])
            ->expectsQuestion('Select allowed roles for app CRUD routes (all, admin, user)', 'admin')
            ->assertExitCode(0);

        $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
        $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

        $this->assertStringContainsString("Route::prefix('app/admin/billing')", $routeFileContents);
        $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);
    }

    public function test_multi_word_module_names_generate_php_safe_feature_test_methods(): void
    {
        $basePath = $this->createTemporaryBasePath();

        $this->runGenerateCommand([
            'module' => 'TempCheck',
            '--scaffold' => 'crud-api',
            '--route-profile' => 'app',
            '--roles' => 'admin',
            '--api-route-profile' => 'protected',
            '--no-file-prompts' => true,
            '--base-path' => $basePath,
        ])->assertExitCode(0);

        $pageTestContents = file_get_contents($basePath.'/tests/Feature/TempCheck/IndexPageTest.php');
        $pageTestContents = is_string($pageTestContents) ? $pageTestContents : '';
        $this->assertStringContainsString('test_guests_access_behavior_for_temp_check_page', $pageTestContents);
        $this->assertStringContainsString('test_authenticated_users_can_visit_temp_check_page', $pageTestContents);
        $this->assertStringNotContainsString('temp check_page', $pageTestContents);

        $apiTestContents = file_get_contents($basePath.'/tests/Feature/TempCheck/IndexApiTest.php');
        $apiTestContents = is_string($apiTestContents) ? $apiTestContents : '';
        $this->assertStringContainsString('test_guests_access_behavior_for_temp_check_api_index', $apiTestContents);
        $this->assertStringContainsString('test_authenticated_users_can_list_temp_check_api_results', $apiTestContents);
        $this->assertStringNotContainsString('temp check_api', $apiTestContents);
    }

    private function createTemporaryBasePath(): string
    {
        $basePath = storage_path('framework/testing/generate-module-'.Str::uuid()->toString());
        $this->temporaryBasePaths[] = $basePath;

        $this->ensureDirectory($basePath.'/app/Modules');
        $this->ensureDirectory($basePath.'/resources/js/modules');
        $this->ensureDirectory($basePath.'/tests/Feature');

        return $basePath;
    }

    private function ensureDirectory(string $path): void
    {
        $filesystem = app(Filesystem::class);

        if (! $filesystem->isDirectory($path)) {
            $filesystem->makeDirectory($path, 0755, true);
        }
    }

    private function assertMigrationFileExists(string $basePath, string $tableName): void
    {
        $migrationFiles = glob($basePath.sprintf('/database/migrations/*_create_%s_table.php', $tableName));
        $migrationFiles = is_array($migrationFiles) ? $migrationFiles : [];

        $this->assertNotEmpty($migrationFiles, sprintf('Expected migration file for table [%s] to be generated.', $tableName));
    }

    private function assertNoMigrationFileExists(string $basePath, string $tableName): void
    {
        $migrationFiles = glob($basePath.sprintf('/database/migrations/*_create_%s_table.php', $tableName));
        $migrationFiles = is_array($migrationFiles) ? $migrationFiles : [];

        $this->assertEmpty($migrationFiles, sprintf('Did not expect migration file for table [%s] to be generated.', $tableName));
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private function runGenerateCommand(array $arguments): PendingCommand
    {
        $pendingCommand = $this->artisan('generate:module', $arguments);

        if (is_int($pendingCommand)) {
            $this->fail('Expected a PendingCommand instance while running generate:module.');
        }

        return $pendingCommand;
    }
}
