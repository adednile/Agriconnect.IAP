<?php
// Convenience wrapper: import schema.sql using the project's helper
// Usage: php set_up_database.php

$runner = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'run_sql.php';
$schema = __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql';
if (!file_exists($runner)) {
    echo "Runner not found: $runner\n";
    exit(1);
}
if (!file_exists($schema)) {
    echo "Schema not found: $schema\n";
    exit(1);
}

echo "Running schema import via helper...\n";
$cmd = escapeshellarg(PHP_BINARY) . " " . escapeshellarg($runner) . " " . escapeshellarg($schema) . " 2>&1";
passthru($cmd, $rc);
if ($rc === 0) {
    echo "Done.\n";
    exit(0);
} else {
    echo "Import failed (exit code: $rc).\n";
    exit($rc);
}
?>
