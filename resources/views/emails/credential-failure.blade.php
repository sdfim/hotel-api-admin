<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Credential Failure Alert</title>
</head>
<body>
    <h2 style="color: red;">CREDENTIAL FAILURE</h2>

    <p><strong>Provider:</strong> {{ strtoupper($provider) }}</p>
    <p><strong>Error Message:</strong></p>
    <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
        {{ is_string($error) ? $error : json_encode($error) }}
    </pre>
    <p><strong>Timestamp:</strong> {{ $timestamp }}</p>

    <hr>
    <p style="font-size: 12px; color: gray;">
        This alert was generated automatically by the OBE system.
    </p>
</body>
</html>
