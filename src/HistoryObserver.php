<?php

namespace Panoscape\History;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
            'meta' => $model->getModelMeta('created'),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => now(),
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
        /**
         * Bypass restoring event
         */
        if(array_key_exists('deleted_at', $changes)) return;
        /**
         * Get meta values that will be stored
         */
        $meta = $model->getModelMeta('updating');
        /**
         * Bypass updating event when meta is empty
         */
        if (!$meta) return;

        $model->morphMany(History::class, 'model')->create([
            'message' => trans('panoscape::history.updating', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]),
            'meta' => $meta,
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => now(),
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
            'meta' => $model->getModelMeta('deleting'),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => now(),
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
            'meta' => $model->getModelMeta('restored'),
            'user_id' => static::getUserID(),
            'user_type' => static::getUserType(),
            'performed_at' => now(),
        ]);
    }

    public static function getModelName($model)
    {
        $class = class_basename($model);
        $key = 'panoscape::history.models.'.Str::snake($class);
        $value =  trans($key);

        return $key == $value ? $class : $value;
    }

    public static function getUserID()
    {
        return static::getAuth()->check() ? static::getAuth()->user()->id : null;
    }

    public static function getUserType()
    {
        // Use getMorphClass() so the stored user_type honors any morph map alias,
        // keeping it consistent with how History::user()'s morphTo() resolves it.
        return static::getAuth()->check() ? static::getAuth()->user()->getMorphClass() : null;
    }

    public static function filter($action)
    {
        if(!static::getAuth()->check()) {
            if(in_array('nobody', config('history.user_blacklist'))) {
                return false;
            }
        }
        else {
            $user = static::getAuth()->user();
            $blacklist = config('history.user_blacklist');
            // Match either the fully-qualified class name or the morph map alias,
            // so the blacklist works regardless of which form is configured.
            if(in_array(get_class($user), $blacklist) || in_array($user->getMorphClass(), $blacklist)) {
                return false;
            }
        }

        return is_null($action) || in_array($action, config('history.events_whitelist'));
    }

    private static function getAuth()
    {
        $guards = config('history.auth_guards');
        if(is_bool($guards) && $guards == true)
            return auth(static::activeGuard());
        if(is_array($guards))
        {
            foreach($guards as $guard)
                if(auth($guard)->check()) return auth($guard);
        }
        return auth();
    }

    private static function activeGuard()
    {
        foreach(array_keys(config('auth.guards')) as $guard)
        {
            if(auth($guard)->check()) return $guard;
        }
        return null;
    }

}
