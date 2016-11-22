<?php

namespace Panoscape\History;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class HistoryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/history.php' => config_path('history.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations')
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/lang', 'panoscape');

        $this->publishes([
            __DIR__.'/lang' => resource_path('lang/vendor/panoscape'),
        ], 'translations');

        Event::subscribe(Listeners\HistoryEventSubscriber::class);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/history.php', 'history');
    }
}