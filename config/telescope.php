<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Telescope Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Telescope will be accessible from. If this
    | setting is null, Telescope will reside under the same domain as the
    | application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('TELESCOPE_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Telescope Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Telescope will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that isn't exposed to users.
    |
    */

    'path' => env('TELESCOPE_PATH', 'telescope'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Telescope's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Telescope watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable all Telescope watchers.
    |
    */

    'enabled' => env('TELESCOPE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Telescope Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Telescope route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web',
        'auth',
        // Custom middleware para proteger Telescope
        \App\Http\Middleware\TelescopeAccess::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Paths & Actions
    |--------------------------------------------------------------------------
    |
    | The following array lists the paths and actions that Telescope will
    | ignore. This is beneficial so you don't clutter your database with
    | data about requests and commands that do not need monitoring.
    |
    */

    'ignore_paths' => [
        '/telescope*',
        '/api/health', // No monitorear el endpoint de health check
        '/horizon*',
        '/api/_ignition*',
    ],

    'ignore_commands' => [
        '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | Telescope. The watchers gather the application's profile data when
    | a request or task is executed. Feel free to customize this list.
    |
    */

    'watchers' => [
        // Watcher de Requests - Monitorea todas las peticiones HTTP
        'requests' => env('TELESCOPE_REQUESTS_WATCHER', true),

        // Watcher de Jobs - Monitorea jobs en cola
        'jobs' => env('TELESCOPE_JOBS_WATCHER', true),

        // Watcher de Commands - Monitorea comandos artisan
        'commands' => env('TELESCOPE_COMMANDS_WATCHER', true),

        // Watcher de Schedule - Monitorea tareas programadas
        'schedule' => env('TELESCOPE_SCHEDULE_WATCHER', true),

        // Watcher de Events - Monitorea eventos dispatchados
        'events' => env('TELESCOPE_EVENTS_WATCHER', true),

        // Watcher de Views - Monitorea rendering de vistas
        // Desactivado por defecto ya que es muy verboso
        'views' => env('TELESCOPE_VIEWS_WATCHER', false),

        // Watcher de Queries - Monitorea queries a base de datos
        // Útil para identificar N+1 queries
        'queries' => env('TELESCOPE_QUERIES_WATCHER', env('TELESCOPE_ENABLED', true)),

        // Watcher de Dumps - Monitorea dump() y dd()
        'dumps' => env('TELESCOPE_DUMPS_WATCHER', true),

        // Watcher de Logs - Monitorea logs de la aplicación
        'logs' => env('TELESCOPE_LOGS_WATCHER', true),

        // Watcher de Mail - Monitorea emails enviados
        'mail' => env('TELESCOPE_MAIL_WATCHER', true),

        // Watcher of Notifications - Monitorea notificaciones enviadas
        'notifications' => env('TELESCOPE_NOTIFICATIONS_WATCHER', true),

        // Watcher de Redis - Monitorea comandos Redis
        'redis' => env('TELESCOPE_REDIS_WATCHER', true),

        // Watcher de Exceptions - Monitorea excepciones
        'exceptions' => env('TELESCOPE_EXCEPTIONS_WATCHER', true),

        // Watcher de Gate - Monitorea checks de autorización
        'gate' => env('TELESCOPE_GATE_WATCHER', true),

        // Watcher de Client Requests - Monitorea requests HTTP salientes (Guzzle)
        'client_requests' => env('TELESCOPE_CLIENT_REQUESTS_WATCHER', true),

        // Watcher de Models - Monitorea eventos de modelos (created, updated, etc)
        'models' => env('TELESCOPE_MODELS_WATCHER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email del Administrador
    |--------------------------------------------------------------------------
    |
    | Define el email que tendrá acceso a Telescope en producción.
    | Este usuario puede acceder independientemente de los roles.
    |
    */

    'admin_email' => env('TELESCOPE_ADMIN_EMAIL', 'admin@pawfectshop.com'),

];
