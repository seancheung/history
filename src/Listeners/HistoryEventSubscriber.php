<?php

namespace Panoscape\History\Listeners;

use Panoscape\History\Events\ModelChanged;
use Panoscape\History\HistoryObserver;
use Panoscape\History\History;

class HistoryEventSubscriber
{
    /**
     * Handle the event.
     *
     * @param  ModelChanged  $event
     * @return void
     */
    public function onModelChanged($event)
    {
        if(!HistoryObserver::filter(null)) return;

        $event->model->morphMany(History::class, 'model')->create([
            'message' => $event->message,
            'meta' => $event->meta,
            'user_id' => HistoryObserver::getUserID(),
            'user_type' => HistoryObserver::getUserType(),
            'performed_at' => time(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \Panoscape\History\Events\ModelChanged::class,
            static::class.'@onModelChanged'
        );
    }
}
