<?php

// Include Composer's autoloader
require __DIR__.'/vendor/autoload.php';

use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;

// Function to retrieve a secret from Secrets Manager
function getSecretValue($secretName, $region)
{
    $client = new SecretsManagerClient([
        'version' => '2017-10-17',
        'region' => $region,
    ]);

    try {
        $result = $client->getSecretValue([
            'SecretId' => $secretName,
        ]);

        if (isset($result['SecretString'])) {
            return $result['SecretString'];
        } else {
            return $result['SecretBinary'];
        }
    } catch (AwsException $e) {
        error_log("Error retrieving secret '{$secretName}': ".$e->getMessage());
        exit(1);
    }
}

// Get APPNAME and REGION from environment variables passed by App Runner
$appName = getenv('APPNAME_FROM_ENV');
$awsRegion = getenv('AWS_REGION');

if (! $appName || ! $awsRegion) {
    error_log('ERROR: APPNAME_FROM_ENV or AWS_REGION environment variables are not set. Cannot load secrets.');
    exit(1);
}

// --- Load only the general application environment variables ---
// Expected secret name: YOUR_APP_NAME-envs
$appEnvsSecretName = "{$appName}-envs";
$appEnvsString = getSecretValue($appEnvsSecretName, $awsRegion);
$appEnvs = json_decode($appEnvsString, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("ERROR: Failed to decode JSON from secret '{$appEnvsSecretName}': ".json_last_error_msg());
    exit(1);
}

// Set environment variables
if (! empty($appEnvs)) {
    foreach ($appEnvs as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        // ДОБАВЬТЕ ЭТУ СТРОКУ ДЛЯ ОТЛАДКИ:
        error_log("DEBUG: Set environment variable: {$key} = ".(is_string($value) ? substr($value, 0, 10).(strlen($value) > 10 ? '...' : '') : 'non-string value'));
    }
}

$envFile = '';
foreach ($appEnvs as $key => $value) {
    $envFile .= "{$key}=\"{$value}\"\n";
}
file_put_contents(__DIR__ . '/.env', $envFile);
error_log('DEBUG: Environment variables loaded and saved to .env file.');
