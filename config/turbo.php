<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Namespaces
    |--------------------------------------------------------------------------
    |
    | Namespaces stripped when generating DOM IDs and classes from models.
    | For example, App\Models\Message becomes "message" instead of
    | "app_models_message". Add your custom namespaces if you use
    | a different folder structure (e.g. Domain\Billing\Models\).
    |
    */

    'model_namespaces' => ['App\\Models\\', 'App\\'],

    /*
    |--------------------------------------------------------------------------
    | Automatic Redirect 303
    |--------------------------------------------------------------------------
    |
    | Turbo Drive requires redirects after form submissions to use HTTP
    | status 303 (See Other) instead of 302. When enabled, the package
    | automatically registers a middleware that converts all redirects
    | to 303 for Turbo visits. Set to false if you prefer to register
    | the TurboMiddleware manually on specific routes.
    |
    */

    'auto_redirect_303' => true,

];
