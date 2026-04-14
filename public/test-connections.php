<?php

/**
 * Diagnostic script to verify service connectivity within the Docker environment.
 * Access this via https://commissions.wateranalytics.com.au/test-connections.php
 */

header('Content-Type: text/plain');

echo "Laravel Service Connectivity Test\n";
echo "================================\n\n";

// 1. Check PHP Extensions
echo "[1] Checking PHP Extensions:\n";
$required_exts = ['pdo_mysql', 'redis', 'bcmath', 'mbstring', 'gd', 'zip', 'intl'];
foreach ($required_exts as $ext) {
    echo "- $ext: " . (extension_loaded($ext) ? "LOADED ✅" : "MISSING ❌") . "\n";
}
echo "\n";

// 2. Check MySQL Connection
echo "[2] Testing MySQL Connection:\n";
$host = getenv('DB_HOST') ?: 'mysql';
$db   = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Result: CONNECTED ✅\n";
    
    // Check for Pulse tables
    $stmt = $pdo->query("SHOW TABLES LIKE 'pulse_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Pulse Tables: " . (count($tables) > 0 ? "FOUND (" . count($tables) . ") ✅" : "NOT FOUND (Run migrations) ❌") . "\n";
    
} catch (Exception $e) {
    echo "Result: FAILED ❌ (" . $e->getMessage() . ")\n";
}
echo "\n";

// 3. Check Redis Connection
echo "[3] Testing Redis Connection:\n";
$redis_host = getenv('REDIS_HOST') ?: 'redis';
$redis_port = getenv('REDIS_PORT') ?: 6379;

try {
    if (!extension_loaded('redis')) {
        throw new Exception("Redis extension not loaded");
    }
    $redis = new Redis();
    if ($redis->connect($redis_host, $redis_port, 2)) {
        echo "Result: CONNECTED ✅\n";
        echo "Ping: " . $redis->ping() . "\n";
    } else {
        echo "Result: FAILED ❌ (Connection failed)\n";
    }
} catch (Exception $e) {
    echo "Result: ERROR ❌ (" . $e->getMessage() . ")\n";
}
echo "\n";

echo "Check complete.\n";
