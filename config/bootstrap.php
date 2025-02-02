<?php

if (isset($_ENV['PLATFORM_RELATIONSHIPS'])) {
    $relationships = json_decode(base64_decode($_ENV['PLATFORM_RELATIONSHIPS']), true);
    if (!empty($relationships['mongodb'][0])) {
        $creds = $relationships['mongodb'][0];
        $host = $creds['host'];
        $port = $creds['port'];
        $username = $creds['username'] ?? 'main';
        $password = $creds['password'] ?? '';
        $database = $creds['path'];

        $_ENV['MONGODB_URL'] = "mongodb://$username:$password@$host:$port/$database";
    }
}