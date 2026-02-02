<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ___________________________________ Custom Channels for Middlewares Logs ___________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________

        'users_jwt_authentication_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/users_jwt_authentication/users_jwt_authentication_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'users_jwt_authentication_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/users_jwt_authentication/users_jwt_authentication_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'is_super_admin_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_super_admin/is_super_admin_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'is_super_admin_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_super_admin/is_super_admin_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'is_client_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_client/is_client_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'is_client_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_client/is_client_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'is_admin_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_admin/is_admin_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'is_admin_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/middlewares_logs/is_admin/is_admin_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ___________________________________ Custom Channels for Controllers Logs ___________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ______________________________________________ Auth Channels _______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'google_login_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/google_login/google_login_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'google_login_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/google_login/google_login_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'logout_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/logout/logout_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'logout_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/logout/logout_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'reload_user_session_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/reload_user_session/reload_user_session_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'reload_user_session_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/auth/reload_user_session/reload_user_session_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________ Clients Channels ______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'get_paginated_clients_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/clients/get_paginated_clients/get_paginated_clients_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_clients_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/clients/get_paginated_clients/get_paginated_clients_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________ Failed Quantity Requests Channels ______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'get_paginated_failed_quantity_requests_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/get_paginated_failed_quantity_requests/get_paginated_failed_quantity_requests_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_failed_quantity_requests_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/get_paginated_failed_quantity_requests/get_paginated_failed_quantity_requests_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_failed_quantity_requests_by_filter_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/get_paginated_failed_quantity_requests_by_filter/get_paginated_failed_quantity_requests_by_filter_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_failed_quantity_requests_by_filter_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/get_paginated_failed_quantity_requests_by_filter/get_paginated_failed_quantity_requests_by_filter_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'toggle_settled_status_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/toggle_settled_status/toggle_settled_status_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'toggle_settled_status_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/failed_quantity_requests/toggle_settled_status/toggle_settled_status_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],


        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ___________________________________________ Categories Channels ____________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'category_creation_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/category_creation/category_creation_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'category_creation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/category_creation/category_creation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'category_creation_page_data_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/category_creation_page_data/category_creation_page_data_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'category_creation_page_data_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/category_creation_page_data/category_creation_page_data_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_category_price_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_category_price/get_category_price_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_category_price_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_category_price/get_category_price_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'delete_category_by_id_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/delete_category_by_id/delete_category_by_id_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'delete_category_by_id_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/delete_category_by_id/delete_category_by_id_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_category_by_id_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_category_by_id/get_category_by_id_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_category_by_id_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_category_by_id/get_category_by_id_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_child_categories_by_name_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_child_categories_by_name/get_child_categories_by_name_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_child_categories_by_name_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_child_categories_by_name/get_child_categories_by_name_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_child_categories_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_child_categories/get_child_categories_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_child_categories_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_child_categories/get_child_categories_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_leaf_categories_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_leaf_categories/get_leaf_categories_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_leaf_categories_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_leaf_categories/get_leaf_categories_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_categories_by_filter_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_paginated_categories_by_filter/get_paginated_categories_by_filter_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_categories_by_filter_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_paginated_categories_by_filter/get_paginated_categories_by_filter_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_categories_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_paginated_categories/get_paginated_categories_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_categories_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_paginated_categories/get_paginated_categories_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_parent_categories_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_parent_categories/get_parent_categories_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_parent_categories_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/get_parent_categories/get_parent_categories_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'update_category_base_data_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_category_base_data/update_category_base_data_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'update_category_base_data_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_category_base_data/update_category_base_data_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'update_image_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_image/update_image_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'update_image_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_image/update_image_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'update_parent_category_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_parent_category/update_parent_category_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'update_parent_category_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/update_parent_category/update_parent_category_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'sync_with_oneclickdz_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/sync_with_oneclickdz/sync_with_oneclickdz_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'sync_with_oneclickdz_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/categories/sync_with_oneclickdz/sync_with_oneclickdz_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // __________________________________________ Orders Items Channels ___________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'delete_order_item_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders_items/delete_order_item/delete_order_item_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'delete_order_item_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders_items/delete_order_item/delete_order_item_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'order_item_creation_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders_items/order_item_creation/order_item_creation_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'order_item_creation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders_items/order_item_creation/order_item_creation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // _____________________________________________ Orders Channels ______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'order_confirmation_fails' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_confirmation/order_confirmation_fails.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'order_confirmation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_confirmation/order_confirmation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'order_cancellation_fails' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_cancellation/order_cancellation_fails.log'),
            'level' => 'error',
            'ignore_exceptions' => true
        ],

        'order_cancellation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_cancellation/order_cancellation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'order_creation_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_creation/order_creation_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'order_creation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/order_creation/order_creation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_my_orders_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_my_orders/get_my_orders_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_my_orders_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_my_orders/get_my_orders_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_order_by_id_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_order_by_id/get_order_by_id_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_order_by_id_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_order_by_id/get_order_by_id_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_orders_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_paginated_orders/get_paginated_orders_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_orders_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_paginated_orders/get_paginated_orders_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_orders_by_filter_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_paginated_orders_by_filter/get_paginated_orders_by_filter_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_orders_by_filter_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/get_paginated_orders_by_filter/get_paginated_orders_by_filter_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'orders_filter_dialog_data_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/orders_filter_dialog_data/orders_filter_dialog_data_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'orders_filter_dialog_data_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/orders_filter_dialog_data/orders_filter_dialog_data_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'update_order_base_data_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/update_order_base_data/update_order_base_data_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'update_order_base_data_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/orders/update_order_base_data/update_order_base_data_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________ Products Channels _____________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'get_paginated_products_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_paginated_products/get_paginated_products_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_products_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_paginated_products/get_paginated_products_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_paginated_products_by_filter_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_paginated_products_by_filter/get_paginated_products_by_filter_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_paginated_products_by_filter_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_paginated_products_by_filter/get_paginated_products_by_filter_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_product_by_id_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_product_by_id/get_product_by_id_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_product_by_id_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_product_by_id/get_product_by_id_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'get_unsold_products_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_unsold_products/get_unsold_products_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_unsold_products_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/get_unsold_products/get_unsold_products_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'products_creation_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/products_creation/products_creation_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'products_creation_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/products_creation/products_creation_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'delete_product_by_id_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/delete_product_by_id/delete_product_by_id_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'delete_product_by_id_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/delete_product_by_id/delete_product_by_id_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'update_product_base_data_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/update_product_base_data/update_product_base_data_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'update_product_base_data_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/products/update_product_base_data/update_product_base_data_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________ Settings Channels ______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'toggle_admin_availability_for_backorder_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/settings/toggle_admin_availability_for_backorder/toggle_admin_availability_for_backorder_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'toggle_admin_availability_for_backorder_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/settings/toggle_admin_availability_for_backorder/toggle_admin_availability_for_backorder_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________ Users Channels ______________________________________________
        // ____________________________________________________________________________________________________________
        // ____________________________________________________________________________________________________________
        'get_my_account_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/users/get_my_account/get_my_account_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'get_my_account_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/users/get_my_account/get_my_account_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],

        'toggle_active_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/users/toggle_active/toggle_active_errors.log'),
            'level' => 'error',
            'ignore_exceptions' => true,
        ],

        'toggle_active_requests' => [
            'driver' => 'single',
            'path' => storage_path('logs/controllers_logs/users/toggle_active/toggle_active_requests.log'),
            'level' => 'info',
            'ignore_exceptions' => true
        ],
    ],

];
