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
    | User blacklist
    |--------------------------------------------------------------
    |
    | Operations performed by users in this array will NOT be recorded.
    | Please add the whole class names. Example: \App\User
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
    
];