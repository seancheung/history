<?php

namespace Panoscape\History;

class HistoryObserver
{
    /**
    * Listen to the Model created event.
    *
    * @param  mixed $model
    * @return void
    */
    public function created($model)
    {   
        if(!static::filter('created')) return;

        $model->morphMany(History::class, 'model')->create([
            'message' => trans('panoscape::history.created', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => time(),
        ]);
    }

    /**
    * Listen to the Model updating event.
    *
    * @param  mixed $model
    * @return void
    */
    public function updating($model)
    {   
        if(!static::filter('updating')) return;

        /*
        * Gets the model's altered values and tracks what had changed
        */
        $changes = $model->getDirty();
        $changed = [];
        foreach ($changes as $key => $value) {
            array_push($changed, ['key' => $key, 'old' => $model->getOriginal($key), 'new' => $model->$key]);
        }

        $model->morphMany(History::class, 'model')->create([
            'message' => trans('panoscape::history.updating', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]),
            'meta' => $changed,
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => time(),
        ]);
    }

    /**
    * Listen to the Model deleting event.
    *
    * @param  mixed $model
    * @return void
    */
    public function deleting($model)
    {   
        if(!static::filter('deleting')) return;

        $model->morphMany(History::class, 'model')->create([
            'message' => trans('panoscape::history.deleting', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => time(),
        ]);
    }

    /**
    * Listen to the Model restored event.
    *
    * @param  mixed $model
    * @return void
    */
    public function restored($model)
    {   
        if(!static::filter('restored')) return;

        $model->morphMany(History::class, 'model')->create([
            'message' => trans('panoscape::history.restored', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => time(),
        ]);
    }

    public static function getModelName($model)
    {
        $class = class_basename($model);
        $key = 'panoscape::history.models.'.snake_case($class);
        $value =  trans($key);

        return $key == $value ? $class : $value;
    }

    public static function getUserID()
    {
        return auth()->check() ? get_class(auth()->user()) : null;
    }

    public static function getUserType()
    {
        return auth()->check() ? auth()->user()->id : null;
    }

    public static function filter($action)
    {
        if(!auth()->check()) {
            if(in_array('nobody', config('history.user_blacklist'))) {
                return false;
            }
        }
        elseif(in_array(get_class(auth()->user()), config('history.user_blacklist'))) {
            return false;
        }

        return is_null($action) || in_array($action, config('history.events_whitelist'));
    }
    
}