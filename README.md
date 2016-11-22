# History
Eloquent model history tracking for Laravel

## Installation

### Composer

```shell
composer require panoscape/history
```

### Service provider

> config/app.php

```php
'providers' => [
    ...
    Panoscape\History\HistoryServiceProvider::class,
];
```

### History

> config/app.php

```php
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

### History

```php
//get the associated model
$history->model();

//get the associated user
//the user is the authorized user when the action is being performed
//it might be null if the history is performed unauthenticatedly
$history->user();
//check user existence
$history->hasUser();

//get the message
$history->message;

//get the meta(only available when it's a updating operation)
//the meta will be an array with the properties changing information
$history->meta;

//get the timestamp the action was performed at
$history->performed_at;
```

A sample message

```
Created Project my_project
```

A sample meta

```php
[
    ['key' => 'name', 'old' => 'myName', 'new' => 'myNewName'],
    ['key' => 'age', 'old' => 10, 'new' => 100],
    ...
]
```

### Custom History

Beyond the built in `created/updating/deleting/restoring` events, you may store custom history record with `ModelChanged` event.

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

Sample

```php
//you may added your own model name language line here
    'models' => [
        'project' => '项目',
        'component_template' => '组件模板',
    ]
```

This will translate your model history into

```
创建 项目 project_001
```

### Filters

You may set whitelist and blacklist in config file. Please follow the description guide in the published config file.
