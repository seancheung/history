<?php

namespace Panoscape\History;

trait HasHistories
{
    /**
     * Get all of the model's histories.
     */
    public function histories()
    {
        return $this->morphMany(History::class, 'model');
    }

    /**
     * Get all of the model's histories.
     *
     * @return void
     */
    public static function bootHasHistories()
    {
        if(!config('history.enabled')) {
            return;
        }

        if(in_array(app()->environment(), config('history.env_blacklist'))) {
            return;
        }

        if(app()->runningInConsole() && !config('history.console_enabled')) {
            return;
        }

        if(app()->runningUnitTests() && !config('history.test_enabled')) {
            return;
        }

        static::observe(HistoryObserver::class);
    }

    /**
     * Get the model's label in history.
     *
     * @return string
     */
    public abstract function getModelLabel();
}