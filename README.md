<p align="center">
<a href="https://github.com/seancheung/history/actions"><img src="https://github.com/seancheung/history/workflows/Test/badge.svg" alt="Test Status"></a>
<a href='https://coveralls.io/github/seancheung/history?branch=master'><img src='https://coveralls.io/repos/github/seancheung/history/badge.svg?branch=master' alt='Coverage Status' /></a>
<a href="https://packagist.org/packages/panoscape/history"><img src="https://poser.pugx.org/panoscape/history/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/panoscape/history"><img src="https://poser.pugx.org/panoscape/history/v" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/panoscape/history"><img src="https://poser.pugx.org/panoscape/history/license" alt="License"></a>
</p>

# History

Eloquent model history tracking for Laravel (NOW WITH AUTOLOAD!)

## Installation

### Composer

Laravel 6.x and above

```shell
composer require panoscape/history
```

Laravel 5.6.x

```shell
composer require "panoscape/history:^1.0"
```

### Service provider and alias

> Only required for Laravel 5.6.x

> NO NEED for version 2.0+ which is auto-loaded with [Package Discovery](https://laravel.com/docs/6.x/packages#package-discovery)


*config/app.php*

```php
'providers' => [
    ...
    Panoscape\History\HistoryServiceProvider::class,
];
'aliases' => [
    ...
    'App\History' => Panoscape\History\History::class,
];
```

### Migration

```shell
php artisan vendor:publish --provider="Panoscape\History\HistoryServiceProvider" --tag=migrations
```

### Config

```shell
php artisan vendor:publish --provider="Panoscape\History\HistoryServiceProvider" --tag=config
```

## Localization

```shell
php artisan vendor:publish --provider="Panoscape\History\HistoryServiceProvider" --tag=translations
```

## Usage

Add `HasOperations` trait to user model.

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Panoscape\History\HasOperations;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasOperations;
}
```

Add `HasHistories` trait to the model that will be tracked.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Panoscape\History\HasHistories;

class Article extends Model
{
    use HasHistories;

    public function getModelLabel()
    {
        return $this->display_name;
    }
}
```

Remember that you'll need to implement the abstract `getModelLabel` method from the trait.
This provides the model instance's name in histories.

### Get histories of a model

```php
$model->histories();
//or dynamic property
$model->histories;
```

### Get operations of a user

```php
$user->operations();
//or dynamic property
$user->operations;
```

### Additional query conditions

Both `histories` and `operations` return Eloquent relationships which also serve as query builders. You can add further constraints by chaining conditions:

```php
// get the lastest 10 records
$model->histories()->orderBy('performed_at', 'desc')->take(10)

// filter by user id
$model->histories()->where('user_id', 10010)
```

### History

```php
//get the associated model
$history->model();

//get the associated user
//the user is the authenticated user when the action is being performed
//it might be null if the history is performed unauthenticatedly
$history->user();
//check user existence
$history->hasUser();

//get the message
$history->message;

//get the meta(only available when it's an updating operation)
//the meta will be an array with the properties changing information
$history->meta;

//get the timestamp the action was performed at
$history->performed_at;
```

Example message

```
Created Project my_project
   │       │         │
   │       │         └───── instance name(returned from `getModelLabel`)
   │       └─────────────── model name(class name or localized name)
   └─────────────────────── event name(default or localized name)
```

Example meta

```php
[
    ['key' => 'name', 'old' => 'myName', 'new' => 'myNewName'],
    ['key' => 'age', 'old' => 10, 'new' => 100],
    ...
]
```

### Custom history

Besides the built in `created/updating/deleting/restoring` events, you may store custom history record with `ModelChanged` event.

```php
use Panoscape\History\Events\ModelChanged;

...
//fire a model changed event
event(new ModelChanged($user, 'User roles updated', $user->roles()->pluck('id')->toArray()));
```

The `ModelChanged` constructor accepts two/three arguments. The first is the associated model instance; the second is the message; the third is optional, which is the meta(array);

### Localization

You may localize the model's type name.

To do that, add the language line to the `models` array in the published language file, with the key being **the class's base name in snake case**.

Example language config

```php
/*
|--------------------------------------------------------------------------
| Tracker Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are used across application for various
| messages that we need to display to the user. You are free to modify
| these language lines according to your application's requirements.
|
*/

'created' => '创建:model:label',

'updating' => 'actualizar :model :label',

'deleting' => ':model :label löschen',

'restored' => ':model:labelを復元',

//you may added your own model name language line here
'models' => [
    'project' => '项目',
    'component_template' => '组件模板',
]
```

This will translate your model history into

```
创建项目project_001
```

### Filters

You may set whitelist and blacklist in config file. Please follow the description guide in the published config file.

```php
/*
|--------------------------------------------------------------
| Events whitelist
|--------------------------------------------------------------
|
| Events in this array will be recorded.
| Available events are: created, updating, deleting, restored
|
*/
'events_whitelist' => [
    'created', 'updating', 'deleting', 'restored',
],

/*
|--------------------------------------------------------------
| Attributes blacklist
|--------------------------------------------------------------
| 
| Please add the whole class names. Example: \App\User:class
| For each model, attributes in its respect array will NOT be recorded into meta when performing update operation.
|
*/
'attributes_blacklist' => [
    // \App\User::class => [
    //     'password'
    // ],
],

/*
|--------------------------------------------------------------
| User blacklist
|--------------------------------------------------------------
|
| Operations performed by users in this array will NOT be recorded.
| Please add the whole class names. Example: \App\User:class
| Use 'nobody' to bypass unauthenticated operations
|
*/
'user_blacklist' => [
    
],
/*
|--------------------------------------------------------------
| Enviroments blacklist
|--------------------------------------------------------------
|
| When application's environment is in the list, tracker will be disabled
|
*/
'env_blacklist' => [
    
],
```

### Auth guards

If your users are using non-default auth guards, you might see all `$history->hasUser()` become `false` even though the history sources were generated by authenticated users.

To fix this, you'll need to enable custom auth guards scanning in config file:

```php
/*
|--------------------------------------------------------------
| Enable auth guards scan
|--------------------------------------------------------------
|
| You only need to enable this if your users are using non-default auth guards.
| In that case, all tracked user operations will be anonymous.
|
| - Set to `true` to use a full scan mode: all auth guards will be checked. However this does not ensure guard priority.
| - Set to an array to scan only specific auth guards(in the given order). e.g. `['web', 'api', 'admin']`
|
*/
'auth_guards' => null
```

### Custom meta

You can define your own method for meta data. By default for `updating` event meta consists of modified keys and for other events meta is `null`.

Just redefine the method `getModelMeta` for the trait.

Example:

```php
class Article extends Model
{
    // if you want to use default trait method, you need to redeclare it with a new name
    use HasHistories {
        getModelMeta as protected traitGetModelMeta;
    };

    ...
    
    public function getModelMeta($event)
    {
        // using defaults for updating
        if($event == 'updating') return $this->traitGetModelMeta($event);
        // passing full model to meta
        // ['key1' => 'value1', 'key2' => 'value2', ...]
        else return $this;
    }
}
```

### Known issues

1. When updating a model, if its model label(attributes returned from `getModelLabel`) has been modified, the history message will use its new attributes, which might not be what you expect.

```php
class Article extends Model
{
    use HasHistories;

    public function getModelLabel()
    {
        return $this->title;
    }
}
// original title is 'my title'
// modify title
$article->title = 'new title';
$article->save();
// the updating history message
// expect: Updating Article my title
// actual: Updating Article new title
```

A workaround

```php
public function getModelLabel()
{
    return $this->getOriginal('title', $this->title);
}
```
