<?php
// tools/run_sql.php
// Usage: php tools\run_sql.php path\to\file.sql

$cwd = __DIR__ . '/..';
require_once $cwd . '/includes/db_connection.php';

if ($argc < 2) {
    echo "Usage: php tools\\run_sql.php path\\to\\file.sql\n";
    exit(1);
}

$path = $argv[1];
if (!file_exists($path)) {
    echo "File not found: $path\n";
    exit(2);
}

$sql = file_get_contents($path);
if ($sql === false) {
    echo "Failed to read file: $path\n";
    exit(3);
}

// Try PDO first
$pdo = null;
if (function_exists('get_pdo')) {
    $pdo = get_pdo();
}

if ($pdo) {
    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);
        $pdo->commit();
        echo "OK: executed SQL via PDO\n";
        exit(0);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "PDO error: " . $e->getMessage() . "\n";
        exit(4);
    }
}

// fallback to mysqli shim / global $conn
if (function_exists('get_mysqli')) {
    $conn = get_mysqli();
} else {
    global $conn;
}

if (empty($conn)) {
    echo "No DB connection available (neither PDO nor mysqli). Check includes/db_connection.php or .env.\n";
    exit(5);
}

// If $conn is an instance of mysqli
if ($conn instanceof mysqli) {
    if (!$conn->multi_query($sql)) {
        echo "MySQLi error: " . $conn->error . "\n";
        exit(6);
    } else {
        do {
            if ($res = $conn->store_result()) {
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "OK: executed SQL via MySQLi\n";
        exit(0);
    }
}

// If $conn is an object with executeQuery
if (is_object($conn) && method_exists($conn, 'executeQuery')) {
    try {
        $conn->executeQuery($sql);
        echo "OK: executed SQL via connection shim\n";
        exit(0);
    } catch (Exception $e) {
        echo "Error executing SQL via shim: " . $e->getMessage() . "\n";
        exit(7);
    }
}

echo "Unsupported connection type.\n";
exit(8);
