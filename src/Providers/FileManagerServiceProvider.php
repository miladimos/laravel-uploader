<?php

namespace Miladimos\FileManager\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Miladimos\FileManager\Console\Commands\InitializePackageCommand;
use Miladimos\FileManager\Console\Commands\InstallPackageCommand;
use Miladimos\FileManager\FileManager;

class FileManagerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/../../config/filemanager.php", 'filemanager');

        $this->registerFacades();

    }

    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->registerConfig();
            // $this->registerPublishesMigrations();
            $this->registerCommands();
            $this->registerTranslations();
            $this->registerRoutes();
        }
    }

    private function registerFacades()
    {
        $this->app->bind('filemanager', function ($app) {
            return new FileManager();
        });
    }

    private function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/filemanager.php' => config_path('filemanager.php')
        ], 'filemanager_config');
    }

    private function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filemanager');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/miladimos/laravel-filemanager'),
        ]);
    }

    private function registerCommands()
    {
        $this->commands([
            InstallPackageCommand::class,
            InitializePackageCommand::class,
        ]);
    }

    private function registerPublishesMigrations()
    {

        if (!class_exists('CreateDirectoriesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_directories_table.stub.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_directories_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }

        if (!class_exists('CreateFilesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_files_table.stub.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_files_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }

//        if (!class_exists('CreateFileGroupsTable')) {
//            $this->publishes([
//                __DIR__ . '/../database/migrations/create_file_groups_table.stub.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_file_groups_table.php'),
//                // you can add any number of migrations here
//            ], 'migrations');
//        }
//        if (!class_exists('CreateFileGroupPivotTable')) {
//            $this->publishes([
//                __DIR__ . '/../database/migrations/create_file_group_pivot_table.stub.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_file_group_pivot_table.php'),
//                // you can add any number of migrations here
//            ], 'migrations');
//        }
    }

    private function registerRoutes()
    {
        Route::group($this->routeConfiguration('web'), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php', 'filemanager-routes');
        });

        Route::group($this->routeConfiguration('api'), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/filemanger-api.php', 'filemanager-routes');
        });
    }

    private function routeConfiguration($uses = 'api')
    {
        if ($uses == 'api') {
            return [
                'prefix' => config('filemanager.routes.api.api_prefix') . '/' . config('filemanager.routes.api.api_version') . '/' . config('filemanager.routes.prefix'),
                'middleware' => config('filemanager.routes.api.middleware'),
            ];
        } else {
            return [
                'prefix' => config('filemanager.routes.prefix'),
                'middleware' => config('filemanager.routes.web.middleware'),
            ];
        }

    }
}
