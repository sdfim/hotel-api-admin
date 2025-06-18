<?php

use App\Support\Services\Logging\Drivers\AwsCloudwatchLogHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;

return [

    'channels' => [
        'cloudwatch' => [
            'driver' => 'monolog',
            'handler' => AwsCloudwatchLogHandler::class,
            'handler_with' => [
                'region'      => env('LOG_CLOUDWATCH_DEFAULT_REGION', 'us-east-1'),
                'version'     => env('LOG_CLOUDWATCH_VERSION', 'latest'),
                'group' => env('LOG_CLOUDWATCH_GROUP_NAME', '/aws/apprunner/fora-stage-engine'),
                'stream' => date('Y-m-d'),
                'retention' => env('LOG_CLOUDWATCH_RETENTION_DAYS', 30),
                'batchSize' => env('LOG_CLOUDWATCH_BATCH_SIZE', 1000),
//                'credentials' => [
//                    'use_aws_environment_credentials' => true,
//                ],
            ],
            'level' => env('LOG_LEVEL', 'debug'),
            'region' => env('LOG_CLOUDWATCH_DEFAULT_REGION', 'us-east-1'),
            'formatter' => JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
            'processors' => [
                \App\Support\Services\Logging\Processors\LogCommandInformationProcessor::class,
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],

];
