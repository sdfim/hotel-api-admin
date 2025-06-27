<?php

// Include Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

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
// Expected secret name: APP_NAME-envs
$appEnvsSecretName = "{$appName}-envs";
$appEnvsString = getSecretValue($appEnvsSecretName, $awsRegion);
$appEnvs = json_decode($appEnvsString, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("ERROR: Failed to decode JSON from secret '{$appEnvsSecretName}': ".json_last_error_msg());
    exit(1);
}

// Set environment variables
if (! empty($appEnvs)) {
    $envFileContent = file_exists(__DIR__ . '/.env') ? file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES) : [];
    $newEnvLines = [];

    foreach ($appEnvs as $key => $value) {
        $escaped = addcslashes($value, "\"\n\r");
        $envLine = "{$key}=\"{$escaped}\"";
        $updated = false;

        // Check if the variable already exists and update it
        foreach ($envFileContent as $index => $line) {
            if (str_starts_with($line, $key.'=')) {
                $envFileContent[$index] = $envLine;
                $updated = true;
                break;
            }
        }

        // If not updated, add as a new line
        if (! $updated) {
            $envFileContent[] = $envLine;
        }

        error_log("DEBUG: Set environment variable: {$key} = ".(is_string($value) ? substr($value, 0, 3).(strlen($value) > 3 ? '...' : '') : 'non-string value'));
    }

    // Write all lines back to the .env file
    file_put_contents(__DIR__ . '/.env', implode("\n", $envFileContent));
    error_log('DEBUG: Environment variables loaded and saved to .env file.');
}
