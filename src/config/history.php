<?php

return [

    /*
    |--------------------------------------------------------------
    | Literally
    |--------------------------------------------------------------
    |
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------
    | History table name
    |--------------------------------------------------------------
    |
    |
    */
    'histories_table' => 'model_histories',

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
    | Enabled when application running in console
    |--------------------------------------------------------------
    |
    | When application is running in console(include seeding)
    |
    */
    'console_enabled' => false,

    /*
    |--------------------------------------------------------------
    | Enabled when application running in unit tests
    |--------------------------------------------------------------
    |
    | When application is running unit tests
    |
    */
    'test_enabled' => false,

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
    'auth_guards' => null,
    
];