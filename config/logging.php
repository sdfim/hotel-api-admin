<?php

use App\Support\Services\Logging\Drivers\AwsCloudwatchLogHandler;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;

return [

    'channels' => [
        'cloudwatch' => [
            'driver' => 'monolog',
            'handler' => AwsCloudwatchLogHandler::class,
            'handler_with' => [
                'client' => new CloudWatchLogsClient([
                    'region' => env('LOG_CLOUDWATCH_DEFAULT_REGION', 'us-east-1'),
                    'version' => env('LOG_CLOUDWATCH_VERSION', 'latest'),
                    'credentials' => [
                        'key' => env('LOG_AWSCLOUDWATCH_ACCESSKEYID', ''),
                        'secret' => env('LOG_AWSCLOUDWATCH_SECRETACCESSKEY', ''),
                    ],
                ]),
                'group' => env('LOG_CLOUDWATCH_GROUP_NAME', '/aws/apprunner/booking-engine'),
                'stream' => date('Y-m-d'),
                'retention' => env('LOG_CLOUDWATCH_RETENTION_DAYS', 30),
                'batchSize' => env('LOG_CLOUDWATCH_BATCH_SIZE', 1000),
            ],
            'level' => env('LOG_LEVEL', 'debug'),
            'region' => env('LOG_CLOUDWATCH_DEFAULT_REGION', 'us-east-1'),
            'formatter' => JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
        ],
        'datadog' => [
            'driver'       => 'monolog',
            'handler'      => StreamHandler::class,
            'handler_with' => [
                'stream' => storage_path('logs/laravel-datadog.log'),
                'level'  => 'debug',
            ],
            'formatter' => JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
        ],
    ],

];
