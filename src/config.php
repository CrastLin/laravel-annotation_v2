<?php
/**
 * @author crastlin@163.com
 * @date 2023-12-30
 *
 * @description  Generate configuration files using commands
 * @using php artisan annotation:config
 */
return [

    /*
     |--------------------------------------------------------------------------
     | Routing annotation configuration
     |--------------------------------------------------------------------------
     */
    'route' => [
        /*
         |--------------------------------------------------------------------------
         | Controller root path configuration
         |--------------------------------------------------------------------------
         |
         | from which common annotations are automatically scanned
         */
        'path' => 'app/Http/Controllers',

        /*
         |--------------------------------------------------------------------------
         | Controller root namespace configuration
         |--------------------------------------------------------------------------
         |
         | Configure the root namespace to obtain complete class information
         */
        'namespace' => 'App\Http\Controllers',

        /*
         |--------------------------------------------------------------------------
         | scan modules director configuration
         |--------------------------------------------------------------------------
         |
         | If modules are set, only classes in the module directory are scanned
         | When not configured, scan all directories by default
         */
        'modules' => ['*'],

        /*
         |--------------------------------------------------------------------------
         | Configuration Single Application Mode
         |--------------------------------------------------------------------------
         |
         | Is it a single application mode
         | If it is a single application mode, then this configuration is true
         */
        'is_single_mode' => false,

        /*
         |--------------------------------------------------------------------------
         | auto create route rules when request case configuration
         |--------------------------------------------------------------------------
         |
         | Whether to enable automatic generation (development mode is recommended)
         | The newly added annotations are automatically created to the routing table
         */
        'auto_create_case' => env('APP_DEBUG'),

        /*
         |--------------------------------------------------------------------------
         | the root route group configuration
         |--------------------------------------------------------------------------
         |
         | options: domain, prefix, middleware
         | for example ['module1' => ['domain' => 'xxx.com', 'prefix' => 'route path prefix', 'middleware' => 'middleware set name（ the name of $routeMiddleware in Http/Kernel.php）‘], 'module2' => ...]
         */
        'root_group' => [],
    ],

    /*
     |--------------------------------------------------------------------------
     | Define file save root directory configuration
     |--------------------------------------------------------------------------
     |
     | Used to save generated routing files and other annotation cache files
     */
    'annotation_path' => 'data/',

    //
    /*
     |--------------------------------------------------------------------------
     | NodeAnnotation configuration
     |--------------------------------------------------------------------------
     |
     | Including Permissions and menu
     */
    'node' => [
        /*
         |--------------------------------------------------------------------------
         | NodeAnnotation configuration
         |--------------------------------------------------------------------------
         |
         | auto create node rules when request case
         | When the controller file changes after activation, it will automatically generate and update nodes to the specified cache or database
         */
        'auto_create_node' => env('ANNOTATION_AUTO_CRATE_NODE', false),

        /*
         |--------------------------------------------------------------------------
         | Data driven configuration
         |--------------------------------------------------------------------------
         |
         | Configure drivers for saving node data
         | When executing annotation to generate menu permission nodes, use this configuration to save data to the corresponding driver
         */
        'driver' => env('ANNOTATION_NODE_STORE_DRIVER', 'database'),

        /*
         |--------------------------------------------------------------------------
         | Connection configuration
         |--------------------------------------------------------------------------
         |
         | the corresponding connection configuration as config/database =>  connections
         */
        'connection' => env('ANNOTATION_NODE_STORE_DATABASE', 'mysql'),

        /*
         |--------------------------------------------------------------------------
         | Save NodeAnnotation configuration
         |--------------------------------------------------------------------------
         |
         | Save the table name of the node
         | You can call NodeAnnotation\Table::builder() to generate a data table
         */
        'table' => 'node',
    ],
    /*
     |--------------------------------------------------------------------------
     | Interceptor configuration
     |--------------------------------------------------------------------------
     |
     | Including all annotation configurations related to interception
     */
    'interceptor' => [
        // Distributed lock configuration
        'lock' => [
            // lock switch, on by default
            'case' => true,
            // Data of response when intercepted
            'response' => [],
            // Lock status validity period setting
            'expire' => 86400,
            // Configure the request token variable for SyncLockByToken
            'token' => '{header.token}',
        ],
        // Parameter input verifier configuration
        'validate' => [
            // validator switch, on by default
            'case' => true,
            // User defined validator's namespace
            'namespace' => 'App\Validator',
        ],
    ],
    /*
     |--------------------------------------------------------------------------
     | Injection configuration
     |--------------------------------------------------------------------------
     |
     | Including all annotation configurations related to injection
     */
    'inject' => [
        // Scan the directory name of the implementation layer
        // located within the interface layer directory
        'impl_path' => 'Impl',
    ],
    /*
     |--------------------------------------------------------------------------
     | Redis connections config
     |--------------------------------------------------------------------------
     |
     | Configure built-in Redis client connection
     */
    'redis' => [
        'master' => [
            // Redis host address
            'host' => env('REDIS_HOST', '127.0.0.1'),
            // Redis port
            'port' => env('REDIS_PORT', 6379),
            // Redis password
            'password' => env('REDIS_PASSWORD'),
            // default serial db
            'db' => env('REDIS_DB', 0),
            // timeout set
            'timeout' => env('REDIS_TIMEOUT', 5),
            // Do you want to enable long connections
            'persistent' => env('REDIS_PERSISTENT', 0),
        ],
        'slave' => [
            // Redis host address
            'host' => env('REDIS_SLAVE_HOST', '127.0.0.1'),
            // Redis port
            'port' => env('REDIS_SLAVE_PORT', 6379),
            // Redis password
            'password' => env('REDIS_SLAVE_PASSWORD'),
            // default serial db
            'db' => env('REDIS_SLAVE_DB', 0),
            // timeout set
            'timeout' => env('REDIS_SLAVE_TIMEOUT', 5),
            // Do you want to enable long connections
            'persistent' => env('REDIS_SLAVE_PERSISTENT', 0),
        ],
    ],
];
