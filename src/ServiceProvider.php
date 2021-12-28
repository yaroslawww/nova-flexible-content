<?php

namespace NovaFlexibleContent;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use NovaFlexibleContent\Commands\Generators\MakeCastCommand;
use NovaFlexibleContent\Commands\Generators\MakeLayoutCommand;
use NovaFlexibleContent\Commands\Generators\MakePresetCommand;
use NovaFlexibleContent\Commands\Generators\MakeResolverCommand;
use NovaFlexibleContent\Commands\Generators\StubPublishCommand;
use NovaFlexibleContent\Http\Middleware\InterceptFlexibleAttributes;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addMiddleware();

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-flexible-content', __DIR__ . '/../dist/js/field.js');
            Nova::style('nova-flexible-content', __DIR__ . '/../dist/css/field.css');
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-flexible-content.php' => config_path('nova-flexible-content.php'),
            ], 'config');

            $this->commands([
                MakeCastCommand::class,
                MakeLayoutCommand::class,
                MakePresetCommand::class,
                MakeResolverCommand::class,
                StubPublishCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-flexible-content.php', 'nova-flexible-content');
    }

    /**
     * Adds required middleware for Nova requests.
     *
     * @return void
     */
    public function addMiddleware()
    {
        $router = $this->app['router'];

        if ($router->hasMiddlewareGroup('nova')) {
            $router->pushMiddlewareToGroup('nova', InterceptFlexibleAttributes::class);

            return;
        }

        if (!$this->app->configurationIsCached()) {
            config()->set('nova.middleware', array_merge(
                config('nova.middleware', []),
                [InterceptFlexibleAttributes::class]
            ));
        }
    }
}
