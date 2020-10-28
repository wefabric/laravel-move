<?php

namespace Uteq\Move;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Uteq\Move\Commands\InstallCommand;
use Uteq\Move\Commands\MoveCommand;
use Uteq\Move\Facades\Move;
use Uteq\Move\Livewire\HeaderSearch;
use Uteq\Move\Livewire\ResourceForm;
use Uteq\Move\Livewire\ResourceShow;
use Uteq\Move\Livewire\ResourceTable;

class MoveServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'move');

        $this->configureComponents();
        $this->configureRoutes();
        $this->configureNamespaces();
        $this->configureBladeDirectives();
        $this->configureCommands();
    }

    public function configureComponents()
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            $this->registerComponent('field.checkbox');
            $this->registerComponent('field.date');
            $this->registerComponent('field.input');
            $this->registerComponent('field.number');
            $this->registerComponent('field.password');
            $this->registerComponent('field.select');

            $this->registerComponent('form.input-error');
            $this->registerComponent('form.label');
            $this->registerComponent('form.row');

            $this->registerComponent('sidebar.link');

            $this->registerComponent('table.filters');
            $this->registerComponent('table.header');
            $this->registerComponent('table.item-actions');

            $this->registerComponent('a');
            $this->registerComponent('action-message');
            $this->registerComponent('button');
            $this->registerComponent('card');
            $this->registerComponent('dialog-modal');
            $this->registerComponent('dropdown');
            $this->registerComponent('dropdown-link');
            $this->registerComponent('form-section');
            $this->registerComponent('header');
            $this->registerComponent('modal');
            $this->registerComponent('profile-dropdown');
            $this->registerComponent('row');
            $this->registerComponent('secondary-button');
            $this->registerComponent('section-title');
            $this->registerComponent('sidebar');
            $this->registerComponent('sidebar-menu');
            $this->registerComponent('status');
            $this->registerComponent('switchable-team');
            $this->registerComponent('table');
            $this->registerComponent('td');
            $this->registerComponent('th');
        });
    }

    /**
     * Register the given component.
     *
     * @param string $component
     * @return void
     */
    protected function registerComponent(string $component)
    {
        Blade::component('move::components.' . $component, 'move-' . $component);
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        Route::group([
            'domain' => config('move.domain', null),
            'prefix' => config('move.path', Move::getPrefix()),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/move.php');
        });
    }

    public function configureNamespaces()
    {
        if (file_exists(Move::generatePathFromNamespace('App\\Move'))) {
            Move::resourceNamespace('App\\Move', '');
        }

        foreach (Move::all() as $alias => $class) {
            if (! isset($class::$model)) {
                continue;
            }

            $this->app->singleton(Move::getPrefix() . '.' . $alias, function () use ($class) {
                $model = $class::$model;

                return new $class(new $model());
            });
        }
    }

    protected function configureBladeDirectives()
    {
        Blade::directive('moveStyles', [MoveBladeDirectives::class, 'moveStyles']);
        Blade::directive('moveScripts', [MoveBladeDirectives::class, 'moveScripts']);
    }

    public function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return null;
        }

        $this->configurePublishers();

        $this->commands([
            MoveCommand::class,
            InstallCommand::class,
        ]);
    }

    public function configurePublishers()
    {
        $this->publishes([
            __DIR__ . '/../config/move.php' => config_path('move.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/move'),
        ], 'views');

        $migrationFileName = 'create_laravel_move_table.php';
        if (! $this->migrationFileExists($migrationFileName)) {
            $this->publishes([
                __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
            ], 'migrations');
        }
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);

        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/move.php', 'move');

        $this->app->singleton('move', \Uteq\Move\Move::class);

        $this->registerComponentAutoDiscovery();

        $this->configureLivewire();
    }

    public function registerComponentAutoDiscovery()
    {
        $this->app->singleton(ResourceFinder::class, function () {
            return new ResourceFinder(new Filesystem, base_path() . '/');
        });
    }

    public function configureLivewire()
    {
        $this->app->afterResolving(BladeCompiler::class, function () {
            if (! class_exists(Livewire::class)) {
                return;
            }

            Livewire::component('livewire.resource-table', ResourceTable::class);
            Livewire::component('livewire.resource-show', ResourceShow::class);
            Livewire::component('livewire.resource-form', ResourceForm::class);

            Livewire::component('header-search', HeaderSearch::class);
        });
    }
}
